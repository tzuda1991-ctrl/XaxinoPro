@extends('Template::layouts.master')
@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 p-3 rounded" style="background: rgba(255,193,7,0.1); border: 1px solid rgba(255,193,7,0.3);">
                <div>
                    <span class="text-white-50 small d-block">@lang('Current Wallet Balance')</span>
                    <h4 class="text--base mb-0 fw-bold">{{ showAmount(auth()->user()->balance) }}</h4>
                </div>
                <a href="{{ route('user.deposit.history', [], false) }}" class="btn btn--base btn--sm">
                    <i class="las la-history"></i> @lang('Deposit History')
                </a>
            </div>

            <form action="{{ route('user.deposit.insert', [], false) }}" method="post" class="deposit-form">
                @csrf
                <input type="hidden" name="currency" value="{{ gs('cur_text') }}">
                <div class="gateway-card">
                    <div class="row justify-content-center gy-sm-4 gy-3">
                        <div class="col-lg-6">
                            <div class="card custom--card">
                                <div class="card-header py-2">
                                    <h6 class="mb-0 text-white"><i class="las la-wallet text--base me-1"></i> @lang('1. Select Payment Method')</h6>
                                </div>
                                <div class="card-body p-2">
                                    <div class="payment-system-list is-scrollable gateway-option-list">
                                        @forelse ($gatewayCurrency as $data)
                                            <label for="{{ titleToKey($data->name) }}_{{ $data->method_code }}"
                                                class="payment-item gateway-option">
                                                <div class="payment-item__info">
                                                    <span class="payment-item__check"></span>
                                                    <span class="payment-item__name">{{ __($data->name) }}</span>
                                                </div>
                                                <div class="payment-item__thumb">
                                                    <img class="payment-item__thumb-img"
                                                        src="{{ getImage(getFilePath('gateway') . '/' . @$data->method->image) }}"
                                                        alt="@lang('payment-thumb')">
                                                </div>
                                                <input class="payment-item__radio gateway-input"
                                                    id="{{ titleToKey($data->name) }}_{{ $data->method_code }}" hidden
                                                    data-gateway='@json($data)' type="radio"
                                                    name="gateway" value="{{ $data->method_code }}"
                                                    @if($loop->first) checked @endif
                                                    data-min-amount="{{ $data->min_amount }}"
                                                    data-max-amount="{{ $data->max_amount }}">
                                            </label>
                                        @empty
                                            <label for="instant_wallet_default" class="payment-item gateway-option">
                                                <div class="payment-item__info">
                                                    <span class="payment-item__check"></span>
                                                    <span class="payment-item__name">@lang('Instant Wallet / UPI Deposit')</span>
                                                </div>
                                                <input class="payment-item__radio gateway-input"
                                                    id="instant_wallet_default" hidden
                                                    data-gateway='{"method_code":101,"name":"Instant Wallet","currency":"{{ gs("cur_text") }}","percent_charge":0,"fixed_charge":0,"rate":1,"min_amount":1,"max_amount":1000000,"method":{"crypto":0}}'
                                                    type="radio" name="gateway" value="101" checked
                                                    data-min-amount="1" data-max-amount="1000000">
                                            </label>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="card custom--card">
                                <div class="card-header py-2">
                                    <h6 class="mb-0 text-white"><i class="las la-coins text--base me-1"></i> @lang('2. Enter Deposit Amount')</h6>
                                </div>
                                <div class="card-body">
                                    <div class="payment-system-list p-2">
                                        <div class="deposit-info">
                                            <div class="deposit-info__title">
                                                <p class="text mb-0">@lang('Amount')</p>
                                            </div>
                                            <div class="deposit-info__input">
                                                <div class="deposit-info__input-group input-group">
                                                    <span class="deposit-info__input-group-text">{{ gs('cur_sym') }}</span>
                                                    <input type="number" step="any" class="form-control form--control amount"
                                                        name="amount" placeholder="@lang('00.00')"
                                                        value="{{ old('amount', 500) }}" autocomplete="off" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="my-3">
                                            <small class="text-white-50 d-block mb-2">@lang('Tap Quick Amount:')</small>
                                            <div class="d-flex flex-wrap gap-2">
                                                <button type="button" class="btn btn-sm btn-outline--base quick-dep-chip" data-val="100">+100</button>
                                                <button type="button" class="btn btn-sm btn-outline--base quick-dep-chip" data-val="500">+500</button>
                                                <button type="button" class="btn btn-sm btn-outline--base quick-dep-chip" data-val="1000">+1,000</button>
                                                <button type="button" class="btn btn-sm btn-outline--base quick-dep-chip" data-val="5000">+5,000</button>
                                                <button type="button" class="btn btn-sm btn-outline--base quick-dep-chip" data-val="10000">+10,000</button>
                                                <button type="button" class="btn btn-sm btn-outline--danger quick-dep-clear">@lang('Clear')</button>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="deposit-info">
                                            <div class="deposit-info__title">
                                                <p class="text has-icon"> @lang('Limit')
                                                    <span></span>
                                                </p>
                                            </div>
                                            <div class="deposit-info__input">
                                                <p class="text"><span class="gateway-limit">1.00 - 1,000,000.00</span>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="deposit-info">
                                            <div class="deposit-info__title">
                                                <p class="text has-icon">@lang('Processing Charge')</p>
                                            </div>
                                            <div class="deposit-info__input">
                                                <p class="text"><span
                                                        class="processing-fee">@lang('0.00')</span> {{ __(gs('cur_text')) }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="deposit-info total-amount pt-3">
                                            <div class="deposit-info__title">
                                                <p class="text">@lang('Total Credited')</p>
                                            </div>
                                            <div class="deposit-info__input">
                                                <p class="text"><span class="final-amount">@lang('500.00')</span>
                                                    {{ __(gs('cur_text')) }}</p>
                                            </div>
                                        </div>

                                        <button type="submit" class="btn btn--base w-100 mt-3 py-3 fw-bold fs-6">
                                            <i class="las la-check-circle me-1"></i> @lang('Deposit Confirm Now')
                                        </button>
                                        <div class="info-text pt-3">
                                            <p class="text small text-white-50 mb-0">@lang('Instant deposit directly credits your playable wallet balance.')</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('style')
    <style>
        .form--control {
            background: unset !important;
        }
        .quick-dep-chip {
            border: 1px solid hsl(var(--base));
            color: #fff;
            background: rgba(255, 193, 7, 0.12);
            border-radius: 6px;
            padding: 5px 10px;
            font-weight: 700;
            font-size: 12px;
        }
        .quick-dep-chip:hover, .quick-dep-chip:active {
            background: hsl(var(--base));
            color: #000;
        }
        .quick-dep-clear {
            border: 1px solid #dc3545;
            color: #ff6b6b;
            background: rgba(220, 53, 69, 0.12);
            border-radius: 6px;
            padding: 5px 10px;
            font-weight: 700;
            font-size: 12px;
        }
    </style>
@endpush

@push('script')
    <script>
        "use strict";
        (function($) {
            var amount = parseFloat($('.amount').val() || 0);
            var gateway, minAmount = 1, maxAmount = 1000000;

            $('.amount').on('input change', function(e) {
                amount = parseFloat($(this).val());
                if (!amount || amount < 0) {
                    amount = 0;
                }
                calculation();
            });

            $('.quick-dep-chip').on('click', function() {
                var addVal = parseFloat($(this).data('val')) || 0;
                var curVal = parseFloat($('.amount').val()) || 0;
                var nextVal = curVal + addVal;
                $('.amount').val(nextVal).trigger('input');
            });

            $('.quick-dep-clear').on('click', function() {
                $('.amount').val('').trigger('input');
            });

            $('.gateway-input').on('change', function(e) {
                gatewayChange();
            });

            function gatewayChange() {
                let gatewayElement = $('.gateway-input:checked');
                if (!gatewayElement.length) {
                    gatewayElement = $('.gateway-input').first();
                    gatewayElement.prop('checked', true);
                }
                gateway = gatewayElement.data('gateway') || {
                    percent_charge: 0,
                    fixed_charge: 0,
                    rate: 1,
                    min_amount: 1,
                    max_amount: 1000000,
                    currency: "{{ gs('cur_text') }}"
                };
                minAmount = parseFloat(gatewayElement.data('min-amount')) || 1;
                maxAmount = parseFloat(gatewayElement.data('max-amount')) || 1000000;
                calculation();
            }

            function calculation() {
                if (!gateway) return;
                $(".gateway-limit").text(minAmount.toFixed(2) + " - " + maxAmount.toFixed(2));
                let percentCharge = parseFloat(gateway.percent_charge) || 0;
                let fixedCharge = parseFloat(gateway.fixed_charge) || 0;
                let totalPercentCharge = amount ? (amount / 100 * percentCharge) : 0;
                let totalCharge = totalPercentCharge + fixedCharge;
                let totalAmount = (amount || 0) + totalCharge;

                $(".final-amount").text(totalAmount.toFixed(2));
                $(".processing-fee").text(totalCharge.toFixed(2));
                $("input[name=currency]").val(gateway.currency || "{{ gs('cur_text') }}");
                $(".gateway-currency").text(gateway.currency || "{{ gs('cur_text') }}");

                if (amount <= 0) {
                    $(".deposit-form button[type=submit]").attr('disabled', true);
                } else {
                    $(".deposit-form button[type=submit]").removeAttr('disabled');
                }
            }

            gatewayChange();
        })(jQuery);
    </script>
@endpush
