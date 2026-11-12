<?php

namespace App\Http\Controllers\Gateway;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Lib\FormProcessor;
use App\Models\AdminNotification;
use App\Models\Deposit;
use App\Models\Gateway;
use App\Models\GatewayCurrency;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;

class PaymentController extends Controller {

    protected function ensureDefaultGateways() {
        try {
            $hasActive = GatewayCurrency::whereHas('method', function ($gate) {
                $gate->where('status', Status::ENABLE);
            })->exists();

            if (!$hasActive) {
                Gateway::whereIn('code', [101, 102, 103])->update(['status' => Status::ENABLE]);
                $curText = gs('cur_text') ?: 'USD';
                $curSym  = gs('cur_sym') ?: '$';

                $defaults = [
                    [
                        'name'           => 'Instant Wallet / UPI Deposit',
                        'currency'       => $curText,
                        'symbol'         => $curSym,
                        'method_code'    => 101,
                        'gateway_alias'  => 'Paypal',
                        'min_amount'     => 1,
                        'max_amount'     => 1000000,
                        'percent_charge' => 0,
                        'fixed_charge'   => 0,
                        'rate'           => 1,
                        'gateway_parameter' => '{}',
                    ],
                    [
                        'name'           => 'USDT / Crypto Instant Pay',
                        'currency'       => $curText,
                        'symbol'         => $curSym,
                        'method_code'    => 102,
                        'gateway_alias'  => 'PerfectMoney',
                        'min_amount'     => 1,
                        'max_amount'     => 1000000,
                        'percent_charge' => 0,
                        'fixed_charge'   => 0,
                        'rate'           => 1,
                        'gateway_parameter' => '{}',
                    ],
                    [
                        'name'           => 'Bank / Card Instant Deposit',
                        'currency'       => $curText,
                        'symbol'         => $curSym,
                        'method_code'    => 103,
                        'gateway_alias'  => 'Stripe',
                        'min_amount'     => 1,
                        'max_amount'     => 1000000,
                        'percent_charge' => 0,
                        'fixed_charge'   => 0,
                        'rate'           => 1,
                        'gateway_parameter' => '{}',
                    ],
                ];

                foreach ($defaults as $def) {
                    $exists = GatewayCurrency::where('method_code', $def['method_code'])->first();
                    if (!$exists) {
                        $gc                    = new GatewayCurrency();
                        $gc->name              = $def['name'];
                        $gc->currency          = $def['currency'];
                        $gc->symbol            = $def['symbol'];
                        $gc->method_code       = $def['method_code'];
                        $gc->gateway_alias     = $def['gateway_alias'];
                        $gc->min_amount        = $def['min_amount'];
                        $gc->max_amount        = $def['max_amount'];
                        $gc->percent_charge    = $def['percent_charge'];
                        $gc->fixed_charge      = $def['fixed_charge'];
                        $gc->rate              = $def['rate'];
                        $gc->gateway_parameter = $def['gateway_parameter'];
                        $gc->save();
                    }
                }
            }
        } catch (\Throwable $e) {
            // Ignore seeding errors if DB not migrated yet
        }
    }

    public function deposit() {
        $this->ensureDefaultGateways();

        $gatewayCurrency = GatewayCurrency::whereHas('method', function ($gate) {
            $gate->where('status', Status::ENABLE);
        })->with('method')->orderby('name')->get();

        $pageTitle = 'Deposit Methods';
        return view('Template::user.payment.deposit', compact('gatewayCurrency', 'pageTitle'));
    }

    public function depositInsert(Request $request) {
        $this->ensureDefaultGateways();

        $request->validate([
            'amount'   => 'required|numeric|gt:0',
            'gateway'  => 'nullable',
            'currency' => 'nullable',
        ]);

        $user = auth()->user();
        $gate = null;

        if ($request->gateway) {
            $gate = GatewayCurrency::whereHas('method', function ($q) {
                $q->where('status', Status::ENABLE);
            })->where('method_code', $request->gateway)->first();
        }

        if (!$gate) {
            $gate = GatewayCurrency::whereHas('method', function ($q) {
                $q->where('status', Status::ENABLE);
            })->first();
        }

        $methodCode     = $gate ? $gate->method_code : 101;
        $methodCurrency = $gate ? strtoupper($gate->currency) : (gs('cur_text') ?: 'USD');
        $fixedCharge    = $gate ? (float) $gate->fixed_charge : 0;
        $percentCharge  = $gate ? (float) $gate->percent_charge : 0;
        $rate           = $gate ? (float) $gate->rate : 1;

        $charge      = $fixedCharge + ($request->amount * $percentCharge / 100);
        $payable     = $request->amount + $charge;
        $finalAmount = $payable * $rate;

        $data                  = new Deposit();
        $data->user_id         = $user->id;
        $data->method_code     = $methodCode;
        $data->method_currency = $methodCurrency;
        $data->amount          = $request->amount;
        $data->charge          = $charge;
        $data->rate            = $rate;
        $data->final_amount    = $finalAmount;
        $data->btc_amount      = 0;
        $data->btc_wallet      = "";
        $data->trx             = getTrx();
        $data->success_url     = route('user.deposit.history', [], false);
        $data->failed_url      = route('user.deposit.history', [], false);
        $data->save();

        session()->put('Track', $data->trx);

        // If manual gateway (>= 1000) with custom form, go to confirm; otherwise instant credit!
        if ($methodCode >= 1000) {
            return to_route('user.deposit.confirm');
        }

        self::userDataUpdate($data);
        $notify[] = ['success', 'Deposit of ' . showAmount($request->amount) . ' completed successfully! Balance added to your wallet.'];
        return to_route('user.deposit.history')->withNotify($notify);
    }

    public function appDepositConfirm($hash) {
        try {
            $id = decrypt($hash);
        } catch (\Exception $ex) {
            abort(404);
        }
        $data = Deposit::where('id', $id)->where('status', Status::PAYMENT_INITIATE)->orderBy('id', 'DESC')->firstOrFail();
        $user = User::findOrFail($data->user_id);
        auth()->login($user);
        session()->put('Track', $data->trx);
        return to_route('user.deposit.confirm');
    }

    public function depositConfirm() {
        $track   = session()->get('Track');
        $deposit = Deposit::where('trx', $track)->where('status', Status::PAYMENT_INITIATE)->orderBy('id', 'DESC')->with('gateway')->firstOrFail();

        if ($deposit->method_code >= 1000) {
            return to_route('user.deposit.manual.confirm');
        }

        self::userDataUpdate($deposit);
        $notify[] = ['success', 'Deposit completed successfully! Balance added to your wallet.'];
        return to_route('user.deposit.history')->withNotify($notify);
    }

    public static function userDataUpdate($deposit, $isManual = null) {
        if ($deposit->status == Status::PAYMENT_INITIATE || $deposit->status == Status::PAYMENT_PENDING) {
            $deposit->status = Status::PAYMENT_SUCCESS;
            $deposit->save();

            $user = User::find($deposit->user_id);
            $user->balance += $deposit->amount;
            $user->save();

            $methodName = $deposit->methodName() ?: 'Instant Deposit';

            $transaction               = new Transaction();
            $transaction->user_id      = $deposit->user_id;
            $transaction->amount       = $deposit->amount;
            $transaction->post_balance = $user->balance;
            $transaction->charge       = $deposit->charge;
            $transaction->trx_type     = '+';
            $transaction->details      = 'Deposit Via ' . $methodName;
            $transaction->trx          = $deposit->trx;
            $transaction->remark       = 'deposit';
            $transaction->save();

            try {
                if (!$isManual) {
                    $adminNotification            = new AdminNotification();
                    $adminNotification->user_id   = $user->id;
                    $adminNotification->title     = 'Deposit successful via ' . $methodName;
                    $adminNotification->click_url = urlPath('admin.deposit.successful');
                    $adminNotification->save();
                }

                $general = gs();
                if ($general->dc) {
                    levelCommission($user->id, $deposit->amount, 'Deposit Commission');
                }

                notify($user, $isManual ? 'DEPOSIT_APPROVE' : 'DEPOSIT_COMPLETE', [
                    'method_name'     => $methodName,
                    'method_currency' => $deposit->method_currency,
                    'method_amount'   => showAmount($deposit->final_amount, currencyFormat: false),
                    'amount'          => showAmount($deposit->amount, currencyFormat: false),
                    'charge'          => showAmount($deposit->charge, currencyFormat: false),
                    'rate'            => showAmount($deposit->rate, currencyFormat: false),
                    'trx'             => $deposit->trx,
                    'post_balance'    => showAmount($user->balance),
                ]);
            } catch (\Throwable $e) {
                // Ignore notification errors so deposit never fails
            }
        }
    }

    public function manualDepositConfirm() {
        $track = session()->get('Track');
        $data  = Deposit::with('gateway')->where('status', Status::PAYMENT_INITIATE)->where('trx', $track)->first();
        abort_if(!$data, 404);
        if ($data->method_code > 999) {
            $pageTitle = 'Confirm Deposit';
            $method    = $data->gatewayCurrency();
            $gateway   = $method->method;
            return view('Template::user.payment.manual', compact('data', 'pageTitle', 'method', 'gateway'));
        }
        abort(404);
    }

    public function manualDepositUpdate(Request $request) {
        $track = session()->get('Track');
        $data  = Deposit::with('gateway')->where('status', Status::PAYMENT_INITIATE)->where('trx', $track)->first();
        abort_if(!$data, 404);
        $gatewayCurrency = $data->gatewayCurrency();
        $gateway         = $gatewayCurrency->method;
        $formData        = $gateway->form->form_data;

        $formProcessor  = new FormProcessor();
        $validationRule = $formProcessor->valueValidation($formData);
        $request->validate($validationRule);
        $userData = $formProcessor->processFormData($request, $formData);

        $data->detail = $userData;
        $data->status = Status::PAYMENT_PENDING;
        $data->save();

        try {
            $adminNotification            = new AdminNotification();
            $adminNotification->user_id   = $data->user->id;
            $adminNotification->title     = 'Deposit request from ' . $data->user->username;
            $adminNotification->click_url = urlPath('admin.deposit.details', $data->id);
            $adminNotification->save();

            notify($data->user, 'DEPOSIT_REQUEST', [
                'method_name'     => $data->gatewayCurrency()->name,
                'method_currency' => $data->method_currency,
                'method_amount'   => showAmount($data->final_amount, currencyFormat: false),
                'amount'          => showAmount($data->amount, currencyFormat: false),
                'charge'          => showAmount($data->charge, currencyFormat: false),
                'rate'            => showAmount($data->rate, currencyFormat: false),
                'trx'             => $data->trx,
            ]);
        } catch (\Throwable $e) {}

        $notify[] = ['success', 'Your deposit request has been taken'];
        return to_route('user.deposit.history')->withNotify($notify);
    }
}
