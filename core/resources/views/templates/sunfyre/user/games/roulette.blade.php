@extends('Template::layouts.master')
@section('content')
    <div class="row gy-3 gx-lg-4 align-items-center game-wrapper">
        <!-- Live History Ribbon & Round Status Banner -->
        <div class="col-12">
            <div class="roulette-top-bar d-flex justify-content-between align-items-center flex-wrap gap-2 px-3 py-2 rounded-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-danger pulse-dot text-uppercase fw-bold" style="font-size: 11px;">
                        <i class="fas fa-circle me-1" style="font-size: 8px;"></i> LIVE TABLE
                    </span>
                    <span id="roundTimerBadge" class="badge bg-warning text-dark fw-bold px-2 py-1" style="font-size: 12px;">
                        <i class="fas fa-clock me-1"></i> <span id="timerSeconds">30</span>s
                    </span>
                    <span id="roundStatusText" class="fw-bold text-success" style="font-size: 13px;">
                        Place Your Bets
                    </span>
                </div>
                <div class="d-flex align-items-center gap-1 overflow-x-auto py-1" id="historyRibbon" style="max-width: 100%; -webkit-overflow-scrolling: touch;">
                    <span class="text-muted small me-1 d-none d-sm-inline" style="font-size: 11px;">History:</span>
                    <span class="history-chip bg-danger">32</span>
                    <span class="history-chip bg-dark">15</span>
                    <span class="history-chip bg-danger">19</span>
                    <span class="history-chip bg-dark">4</span>
                    <span class="history-chip bg-success">0</span>
                    <span class="history-chip bg-danger">21</span>
                    <span class="history-chip bg-dark">2</span>
                </div>
            </div>
            <!-- Countdown progress bar -->
            <div class="progress mt-1" style="height: 4px; background: rgba(255,255,255,0.1);">
                <div id="roundProgressBar" class="progress-bar bg-warning progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%;"></div>
            </div>
        </div>

        <!-- Wheel Section -->
        <div class="col-lg-5 col-md-6 mb-2">
            <div class="roulette-wheel-box p-1 p-sm-2 text-center rounded-4 shadow-sm" style="background: radial-gradient(circle at center, #151e2c 0%, #0a0f17 100%); border: 1px solid rgba(255, 215, 0, 0.25);">
                <div class="roulette-canvas-wrapper d-flex justify-content-center align-items-center" style="position: relative; width: 100%;">
                    <canvas id="rouletteCanvas" style="display: block; margin: 0 auto; filter: drop-shadow(0 6px 16px rgba(0,0,0,0.6)); max-width: 100%; height: auto;"></canvas>
                </div>
            </div>
        </div>

        <!-- Controls & Chips Section -->
        <div class="col-lg-7 col-md-6 mb-2">
            <div class="headtail-wrapper game-contet__sm p-2 p-sm-3 rounded-4 shadow" style="background: rgba(18, 26, 38, 0.85); border: 1px solid rgba(255, 255, 255, 0.08);">
                <!-- Balance & Mode Switch -->
                <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                    <div>
                        <small class="text-muted text-uppercase d-block" style="font-size: 11px;">
                            {{ $isDemo ? trans('Demo Wallet') : trans('Real Wallet') }}
                        </small>
                        <h4 class="game-contet-title mb-0 fw-bold text-warning">
                            <span class="user-balance">{{ showAmount($balance, currencyFormat: false) }}</span>
                            <small class="fs-6 text-white">{{ __(gs('cur_text')) }}</small>
                        </h4>
                    </div>
                    <div>
                        @if($isDemo)
                            <a href="{{ route('user.play.game', $game->alias) }}" class="btn btn-sm btn-success px-3 py-1 shadow-sm fw-bold" style="border-radius: 8px; font-size: 12px;">
                                <i class="fas fa-coins me-1"></i> @lang('Real Play (10,000 Balance)')
                            </a>
                        @else
                            <a href="{{ route('user.play.game', [$game->alias, 'demo']) }}" class="btn btn-sm btn-secondary px-3 py-1 shadow-sm fw-bold" style="border-radius: 8px; font-size: 12px;">
                                <i class="fas fa-gamepad me-1"></i> @lang('Demo Mode')
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Chips Selection Tray -->
                <div class="chips-container my-2 p-2 rounded-3 text-center">
                    <div class="d-flex justify-content-between align-items-center mb-1 px-1">
                        <small class="text-white-50 fw-bold text-uppercase" style="font-size: 11px;">
                            <i class="fas fa-coins me-1 text-warning"></i> Select Chip & Tap on Table
                        </small>
                        <span class="badge bg-dark text-warning border border-warning" id="activeChipLabel" style="font-size: 11px;">
                            Active: 100
                        </span>
                    </div>
                    <div class="d-flex justify-content-center align-items-center gap-2 flex-wrap" id="chipsTray">
                        <!-- 10 Chip -->
                        <div class="chip-item chip-10" data-val="10">
                            <span class="chip-val">10</span>
                        </div>
                        <!-- 20 Chip -->
                        <div class="chip-item chip-20" data-val="20">
                            <span class="chip-val">20</span>
                        </div>
                        <!-- 50 Chip -->
                        <div class="chip-item chip-50" data-val="50">
                            <span class="chip-val">50</span>
                        </div>
                        <!-- 100 Chip (Default) -->
                        <div class="chip-item chip-100 active" data-val="100">
                            <span class="chip-val">100</span>
                        </div>
                        <!-- 200 Chip -->
                        <div class="chip-item chip-200" data-val="200">
                            <span class="chip-val">200</span>
                        </div>
                        <!-- 500 Chip -->
                        <div class="chip-item chip-500" data-val="500">
                            <span class="chip-val">500</span>
                        </div>
                    </div>
                </div>

                <!-- Live Bet Summary & Action Buttons -->
                <div class="d-flex justify-content-between align-items-center bg-dark bg-opacity-50 p-2 rounded-3 mb-2 border border-secondary border-opacity-25">
                    <div>
                        <small class="text-muted d-block" style="font-size: 11px;">TOTAL BET</small>
                        <span class="fw-bold text-white fs-5" id="totalBetDisplay">0.00</span>
                        <small class="text-warning fw-bold">{{ gs('cur_text') }}</small>
                    </div>
                    <div class="d-flex gap-1">
                        <button type="button" class="btn btn-sm btn-outline-danger px-2 py-1" id="clearAllBetsBtn" title="Clear all chips" style="font-size: 12px;">
                            <i class="fas fa-trash-alt me-1"></i> Clear
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-warning px-2 py-1" id="doubleBetsBtn" title="Double all chips" style="font-size: 12px;">
                            <i class="fas fa-times me-1"></i> 2x Double
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-info px-2 py-1" id="undoBetBtn" title="Undo last chip" style="font-size: 12px;">
                            <i class="fas fa-undo"></i>
                        </button>
                    </div>
                </div>

                <!-- Instant Spin Button -->
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn--gradient play-btn py-2 fs-5 fw-bold text-uppercase shadow" id="spinNowBtn">
                        <i class="fas fa-bolt me-2 text-warning"></i> Spin Now (<span id="btnTimerCount">30</span>s)
                    </button>
                </div>

                <!-- Help & Sound Controls -->
                <div class="d-flex justify-content-between align-items-center mt-2 px-1">
                    <small class="text-muted" style="font-size: 11px;">
                        <i class="fas fa-info-circle me-1"></i> Min: {{ showAmount($game->min_limit) }} | Max: {{ showAmount($game->max_limit) }}
                    </small>
                    <div class="d-flex align-items-center gap-3">
                        <button type="button" class="btn btn-sm btn-link text-white p-0 text-decoration-none" data-bs-toggle="modal" data-bs-target="#exampleModalCenter">
                            <i class="fas fa-question-circle me-1"></i> Rules
                        </button>
                        <button type="button" class="sound--btn audioBtn btn btn-sm btn-link text-warning p-0">
                            <i class="fas fa-volume-up"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 100% Fit Zero-Scroll Mobile Betting Table -->
        <div class="col-12">
            <div class="game--card p-2 p-md-3 rounded-4 shadow">
                <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                    <span class="text-warning fw-bold small">
                        <i class="fas fa-hand-pointer me-1"></i> ROULETTE BOARD <small class="text-white-50 fw-normal d-none d-sm-inline">(Tap any number or box to place chips)</small>
                    </span>
                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 text-white" id="rebetBtn" style="font-size: 11px;">
                        <i class="fas fa-redo-alt me-1"></i> Repeat Bet
                    </button>
                </div>

                <div class="all-number d-block roulette-wrapper">
                    <table id="rouletteTable">
                        <tbody>
                            <!-- Numbers Row 1 -->
                            <tr class="numbers">
                                <td class="zero spot-cell bg-transparent" data-spot="0" rowspan="3">
                                    <span class="number-item">0</span>
                                    <div class="chip-badge"></div>
                                </td>
                                <td class="spot-cell bg-danger redEl" data-spot="3"><span class="number-item">3</span><div class="chip-badge"></div></td>
                                <td class="spot-cell bg-dark blackEl" data-spot="6"><span class="number-item">6</span><div class="chip-badge"></div></td>
                                <td class="spot-cell bg-danger redEl" data-spot="9"><span class="number-item">9</span><div class="chip-badge"></div></td>
                                <td class="spot-cell bg-danger redEl" data-spot="12"><span class="number-item">12</span><div class="chip-badge"></div></td>
                                <td class="spot-cell bg-dark blackEl" data-spot="15"><span class="number-item">15</span><div class="chip-badge"></div></td>
                                <td class="spot-cell bg-danger redEl" data-spot="18"><span class="number-item">18</span><div class="chip-badge"></div></td>
                                <td class="spot-cell bg-danger redEl" data-spot="21"><span class="number-item">21</span><div class="chip-badge"></div></td>
                                <td class="spot-cell bg-dark blackEl" data-spot="24"><span class="number-item">24</span><div class="chip-badge"></div></td>
                                <td class="spot-cell bg-danger redEl" data-spot="27"><span class="number-item">27</span><div class="chip-badge"></div></td>
                                <td class="spot-cell bg-danger redEl" data-spot="30"><span class="number-item">30</span><div class="chip-badge"></div></td>
                                <td class="spot-cell bg-dark blackEl" data-spot="33"><span class="number-item">33</span><div class="chip-badge"></div></td>
                                <td class="spot-cell bg-danger redEl" data-spot="36"><span class="number-item">36</span><div class="chip-badge"></div></td>
                                <td class="spot-cell column-bet p-0 twByOne1" data-spot="2_1_1">
                                    <button class="btn-color" type="button">2:1</button>
                                    <div class="chip-badge"></div>
                                </td>
                            </tr>
                            <!-- Numbers Row 2 -->
                            <tr class="numbers">
                                <td class="spot-cell bg-dark blackEl" data-spot="2"><span class="number-item">2</span><div class="chip-badge"></div></td>
                                <td class="spot-cell bg-danger redEl" data-spot="5"><span class="number-item">5</span><div class="chip-badge"></div></td>
                                <td class="spot-cell bg-dark blackEl" data-spot="8"><span class="number-item">8</span><div class="chip-badge"></div></td>
                                <td class="spot-cell bg-dark blackEl" data-spot="11"><span class="number-item">11</span><div class="chip-badge"></div></td>
                                <td class="spot-cell bg-danger redEl" data-spot="14"><span class="number-item">14</span><div class="chip-badge"></div></td>
                                <td class="spot-cell bg-dark blackEl" data-spot="17"><span class="number-item">17</span><div class="chip-badge"></div></td>
                                <td class="spot-cell bg-dark blackEl" data-spot="20"><span class="number-item">20</span><div class="chip-badge"></div></td>
                                <td class="spot-cell bg-danger redEl" data-spot="23"><span class="number-item">23</span><div class="chip-badge"></div></td>
                                <td class="spot-cell bg-dark blackEl" data-spot="26"><span class="number-item">26</span><div class="chip-badge"></div></td>
                                <td class="spot-cell bg-dark blackEl" data-spot="29"><span class="number-item">29</span><div class="chip-badge"></div></td>
                                <td class="spot-cell bg-danger redEl" data-spot="32"><span class="number-item">32</span><div class="chip-badge"></div></td>
                                <td class="spot-cell bg-dark blackEl" data-spot="35"><span class="number-item">35</span><div class="chip-badge"></div></td>
                                <td class="spot-cell column-bet p-0 twByOne2" data-spot="2_1_2">
                                    <button class="btn-color" type="button">2:1</button>
                                    <div class="chip-badge"></div>
                                </td>
                            </tr>
                            <!-- Numbers Row 3 -->
                            <tr class="numbers">
                                <td class="spot-cell bg-danger redEl" data-spot="1"><span class="number-item">1</span><div class="chip-badge"></div></td>
                                <td class="spot-cell bg-dark blackEl" data-spot="4"><span class="number-item">4</span><div class="chip-badge"></div></td>
                                <td class="spot-cell bg-danger redEl" data-spot="7"><span class="number-item">7</span><div class="chip-badge"></div></td>
                                <td class="spot-cell bg-dark blackEl" data-spot="10"><span class="number-item">10</span><div class="chip-badge"></div></td>
                                <td class="spot-cell bg-dark blackEl" data-spot="13"><span class="number-item">13</span><div class="chip-badge"></div></td>
                                <td class="spot-cell bg-danger redEl" data-spot="16"><span class="number-item">16</span><div class="chip-badge"></div></td>
                                <td class="spot-cell bg-danger redEl" data-spot="19"><span class="number-item">19</span><div class="chip-badge"></div></td>
                                <td class="spot-cell bg-dark blackEl" data-spot="22"><span class="number-item">22</span><div class="chip-badge"></div></td>
                                <td class="spot-cell bg-danger redEl" data-spot="25"><span class="number-item">25</span><div class="chip-badge"></div></td>
                                <td class="spot-cell bg-dark blackEl" data-spot="28"><span class="number-item">28</span><div class="chip-badge"></div></td>
                                <td class="spot-cell bg-dark blackEl" data-spot="31"><span class="number-item">31</span><div class="chip-badge"></div></td>
                                <td class="spot-cell bg-danger redEl" data-spot="34"><span class="number-item">34</span><div class="chip-badge"></div></td>
                                <td class="spot-cell column-bet p-0 twByOne3" data-spot="2_1_3">
                                    <button class="btn-color" type="button">2:1</button>
                                    <div class="chip-badge"></div>
                                </td>
                            </tr>
                            <!-- Dozens Row -->
                            <tr>
                                <td class="border-0"></td>
                                <td class="spot-cell text-center oneToTw" data-spot="1_12" colspan="4">
                                    <button class="w-100 btn-color" type="button"><span>1 to 12</span></button>
                                    <div class="chip-badge"></div>
                                </td>
                                <td class="spot-cell text-center thrtToTf" data-spot="13_24" colspan="4">
                                    <button class="w-100 btn-color" type="button"><span>13 to 24</span></button>
                                    <div class="chip-badge"></div>
                                </td>
                                <td class="spot-cell text-center twfToTs" data-spot="25_36" colspan="4">
                                    <button class="w-100 btn-color" type="button"><span>25 to 36</span></button>
                                    <div class="chip-badge"></div>
                                </td>
                                <td class="border-0"></td>
                            </tr>
                            <!-- Outside Bets Row -->
                            <tr>
                                <td class="border-0"></td>
                                <td class="spot-cell text-center oneToEt" data-spot="1_18" colspan="2">
                                    <button class="w-100 btn-color" type="button"><span>1 to 18</span></button>
                                    <div class="chip-badge"></div>
                                </td>
                                <td class="spot-cell text-center even" data-spot="even" colspan="2">
                                    <button class="w-100 btn-color" type="button"><span>Even</span></button>
                                    <div class="chip-badge"></div>
                                </td>
                                <td class="spot-cell bg-danger text-center red" data-spot="red" colspan="2">
                                    <button class="w-100 btn-color" type="button"><span>Red</span></button>
                                    <div class="chip-badge"></div>
                                </td>
                                <td class="spot-cell bg-dark text-center black" data-spot="black" colspan="2">
                                    <button class="w-100 btn-color" type="button"><span>Black</span></button>
                                    <div class="chip-badge"></div>
                                </td>
                                <td class="spot-cell text-center odd" data-spot="odd" colspan="2">
                                    <button class="w-100 btn-color" type="button"><span>Odd</span></button>
                                    <div class="chip-badge"></div>
                                </td>
                                <td class="spot-cell text-center nineteenTtsix" data-spot="19_36" colspan="2">
                                    <button class="w-100 btn-color" type="button"><span>19 to 36</span></button>
                                    <div class="chip-badge"></div>
                                </td>
                                <td class="border-0"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Instruction Modal -->
    <div class="modal custom--modal fade" id="exampleModalCenter">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content section--bg">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Roulette Game Rules & Payouts')</h5>
                    <span class="close" data-bs-dismiss="modal" type="button" aria-label="Close">
                        <i class="las la-times"></i>
                    </span>
                </div>
                <div class="modal-body">
                    <ul class="list-unstyled lh-lg">
                        <li><strong>Straight Up (Single Number 0-36):</strong> Pays 36 to 1</li>
                        <li><strong>Dozens (1-12, 13-24, 25-36):</strong> Pays 3 to 1</li>
                        <li><strong>Columns (2:1):</strong> Pays 3 to 1</li>
                        <li><strong>Even / Odd, Red / Black, 1-18 / 19-36:</strong> Pays 2 to 1</li>
                    </ul>
                    @php echo $game->instruction @endphp
                </div>
            </div>
        </div>
    </div>
@endsection

@push('style-lib')
    <link href="{{ asset('assets/global/css/game/roulette.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/global/css/game/roulette-wheel.css') }}" rel="stylesheet">
@endpush

@push('script-lib')
    <script src="{{ asset('assets/global/js/soundControl.js') }}"></script>
    <script src="{{ asset('assets/global/js/game/hammer.min.js') }}"></script>
    <script src="{{ asset('assets/global/js/game/luxury-roulette.js') }}"></script>
@endpush

@push('script')
    <script>
        (function($) {
            'use strict';

            var minLimit = Number("{{ $game->min_limit }}") || 1;
            var maxLimit = Number("{{ $game->max_limit }}") || 10000;
            var currency = "{{ gs('cur_text') }}";
            var csrfToken = "{{ csrf_token() }}";
            var investUrl = "{{ route('user.play.invest', ['roulette', @$isDemo]) }}";
            var gameEndUrl = "{{ route('user.play.end', ['roulette', @$isDemo]) }}";
            var audioAssetPath = `{{ asset('assets/audio') }}`;
            var redNumbers = [32, 19, 21, 25, 34, 27, 36, 30, 23, 5, 16, 1, 14, 9, 18, 7, 12, 3];

            // Chips State
            var selectedChipValue = 100;
            var placedBets = {}; // { spot: amount }
            var betHistoryStack = []; // For Undo
            var lastRoundBets = {}; // For Rebet

            // Round State
            var roundDuration = 30; // 30s continuous cycle
            var currentSeconds = 30;
            var roundState = 'BETTING'; // 'BETTING' (30-5s), 'CLOSED' (5-0s), 'SPINNING', 'RESULT'
            var roundTimerInterval = null;
            var isWheelSpinning = false;

            // Initialize Chip Selector
            $('.chip-item').on('click', function() {
                var val = parseInt($(this).data('val'), 10);
                if (val > 0) {
                    selectedChipValue = val;
                    $('.chip-item').removeClass('active');
                    $(this).addClass('active');
                    $('#activeChipLabel').text('Active: ' + selectedChipValue);
                    if (typeof playAudio === 'function') playAudio(audioAssetPath, "click.mp3");
                }
            });

            // Sync Table Chips & Badges
            function renderTableChips() {
                var total = 0;
                $('.spot-cell').removeClass('has-chips');
                $('.chip-badge').empty().hide();

                for (var spot in placedBets) {
                    var amount = placedBets[spot];
                    if (amount > 0) {
                        total += amount;
                        var $cell = $('.spot-cell[data-spot="' + spot + '"]');
                        $cell.addClass('has-chips');
                        var $badge = $cell.find('.chip-badge');
                        $badge.html('<span class="table-mini-chip">' + formatChipAmount(amount) + '</span>').show();
                    }
                }

                $('#totalBetDisplay').text(total.toLocaleString());
                return total;
            }

            function formatChipAmount(amt) {
                if (amt >= 1000) {
                    return (amt / 1000).toFixed(amt % 1000 === 0 ? 0 : 1) + 'k';
                }
                return amt;
            }

            // Spot Click Handlers
            $('.spot-cell').on('click', function(e) {
                if (roundState !== 'BETTING') {
                    notify('error', 'Bets are closed for this round!');
                    return;
                }

                var spot = $(this).data('spot');
                if (spot === undefined || spot === null) return;

                spot = spot.toString();
                placedBets[spot] = (placedBets[spot] || 0) + selectedChipValue;
                betHistoryStack.push({ spot: spot, amount: selectedChipValue });

                if (typeof playAudio === 'function') playAudio(audioAssetPath, "click.mp3");
                renderTableChips();
            });

            // Clear Bets
            $('#clearAllBetsBtn').on('click', function() {
                if (roundState !== 'BETTING') return;
                placedBets = {};
                betHistoryStack = [];
                renderTableChips();
                if (typeof playAudio === 'function') playAudio(audioAssetPath, "click.mp3");
            });

            // Undo Last Bet
            $('#undoBetBtn').on('click', function() {
                if (roundState !== 'BETTING' || !betHistoryStack.length) return;
                var last = betHistoryStack.pop();
                if (placedBets[last.spot]) {
                    placedBets[last.spot] -= last.amount;
                    if (placedBets[last.spot] <= 0) {
                        delete placedBets[last.spot];
                    }
                }
                renderTableChips();
                if (typeof playAudio === 'function') playAudio(audioAssetPath, "click.mp3");
            });

            // Double Bets
            $('#doubleBetsBtn').on('click', function() {
                if (roundState !== 'BETTING') return;
                for (var spot in placedBets) {
                    placedBets[spot] = placedBets[spot] * 2;
                }
                renderTableChips();
                if (typeof playAudio === 'function') playAudio(audioAssetPath, "click.mp3");
            });

            // Repeat Bet
            $('#rebetBtn').on('click', function() {
                if (roundState !== 'BETTING') return;
                if (Object.keys(lastRoundBets).length > 0) {
                    placedBets = JSON.parse(JSON.stringify(lastRoundBets));
                    renderTableChips();
                    if (typeof playAudio === 'function') playAudio(audioAssetPath, "click.mp3");
                } else {
                    notify('info', 'No previous bets to repeat');
                }
            });

            // Spin Now Button
            $('#spinNowBtn').on('click', function() {
                if (isWheelSpinning) return;
                var total = renderTableChips();
                if (total <= 0) {
                    notify('error', 'Please place your chips on the table first!');
                    return;
                }
                // Fast-forward to spin
                currentSeconds = 1;
                tickRoundTimer();
            });

            // Continuous Round Loop Timer
            function startRoundLoop() {
                if (roundTimerInterval) clearInterval(roundTimerInterval);
                currentSeconds = roundDuration;
                roundState = 'BETTING';
                updateTimerUI();

                roundTimerInterval = setInterval(function() {
                    currentSeconds--;
                    tickRoundTimer();
                }, 1000);
            }

            function tickRoundTimer() {
                if (currentSeconds > 5) {
                    roundState = 'BETTING';
                } else if (currentSeconds > 0 && currentSeconds <= 5) {
                    roundState = 'CLOSED';
                } else if (currentSeconds <= 0) {
                    clearInterval(roundTimerInterval);
                    executeRoundSpin();
                    return;
                }
                updateTimerUI();
            }

            function updateTimerUI() {
                $('#timerSeconds').text(currentSeconds);
                $('#btnTimerCount').text(currentSeconds);

                var pct = (currentSeconds / roundDuration) * 100;
                $('#roundProgressBar').css('width', pct + '%');

                if (roundState === 'BETTING') {
                    $('#roundStatusText').text('🟢 PLACE YOUR BETS').removeClass('text-danger text-warning').addClass('text-success');
                    $('#roundTimerBadge').removeClass('bg-danger bg-secondary').addClass('bg-warning text-dark');
                    $('#rouletteTable').removeClass('table-locked');
                    $('#spinNowBtn').prop('disabled', false).html('<i class="fas fa-bolt me-2 text-warning"></i> Spin Now (' + currentSeconds + 's)');
                } else if (roundState === 'CLOSED') {
                    $('#roundStatusText').text('🔴 BETS CLOSED - NO MORE BETS').removeClass('text-success text-warning').addClass('text-danger');
                    $('#roundTimerBadge').removeClass('bg-warning text-dark bg-secondary').addClass('bg-danger text-white');
                    $('#rouletteTable').addClass('table-locked');
                    $('#spinNowBtn').prop('disabled', true).html('<i class="fas fa-lock me-2"></i> Bets Closed (' + currentSeconds + 's)');
                }
            }

            // Execute Wheel Spin (Both for real bet or auto round)
            function executeRoundSpin() {
                isWheelSpinning = true;
                roundState = 'SPINNING';
                $('#rouletteTable').addClass('table-locked');
                $('#roundStatusText').text('🎰 WHEEL SPINNING...').removeClass('text-success text-danger').addClass('text-warning');
                $('#spinNowBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Spinning...');

                var totalBet = renderTableChips();
                var hasActiveBets = (totalBet > 0);

                if (hasActiveBets) {
                    lastRoundBets = JSON.parse(JSON.stringify(placedBets));

                    // Send Real Bets to Backend
                    $.ajax({
                        url: investUrl,
                        type: 'POST',
                        dataType: 'json',
                        headers: { "X-CSRF-TOKEN": csrfToken },
                        data: {
                            _token: csrfToken,
                            invest: totalBet,
                            bets: placedBets,
                            choose: Object.keys(placedBets).join(',')
                        },
                        success: function(response) {
                            if (response.error) {
                                notify('error', response.error);
                                finishRoundSpin(Math.floor(Math.random() * 37), null, 0);
                                return;
                            }
                            if (response.errors) {
                                var msg = Array.isArray(response.errors) ? response.errors.join('<br>') : response.errors;
                                notify('error', msg);
                                finishRoundSpin(Math.floor(Math.random() * 37), null, 0);
                                return;
                            }

                            if (response.balance) {
                                $('.user-balance').text(response.balance);
                            }

                            var winNumber = parseInt(response.result, 10);
                            finishRoundSpin(winNumber, response.game_log_id, totalBet);
                        },
                        error: function(xhr) {
                            // On network error, still spin gracefully
                            var errText = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error processing bet';
                            notify('error', errText);
                            finishRoundSpin(Math.floor(Math.random() * 37), null, 0);
                        }
                    });
                } else {
                    // No bets placed in this round — wheel spins continuously like a live casino!
                    var randomPocket = Math.floor(Math.random() * 37);
                    finishRoundSpin(randomPocket, null, 0);
                }
            }

            // Initialize Luxury Canvas Roulette Wheel
            var luxuryWheel = new LuxuryRouletteWheel('rouletteCanvas');

            // Animate Wheel to Winning Number & Settle Round
            function finishRoundSpin(winningNumber, gameLogId, totalBet) {
                if (typeof playAudio === 'function') {
                    playAudio(audioAssetPath, "spin.mp3");
                }

                // Spin the Luxury Canvas Wheel to the exact target pocket
                luxuryWheel.spinTo(winningNumber, function(finalNum) {
                    var winColor = 'black';
                    if (redNumbers.indexOf(finalNum) !== -1) {
                        winColor = 'red';
                    } else if (finalNum === 0) {
                        winColor = 'green';
                    }

                    // Add to top history ribbon
                    var histClass = winColor === 'red' ? 'bg-danger' : (winColor === 'green' ? 'bg-success' : 'bg-dark');
                    $('#historyRibbon').prepend('<span class="history-chip ' + histClass + ' animate__animated animate__bounceIn">' + finalNum + '</span>');
                    if ($('#historyRibbon .history-chip').length > 10) {
                        $('#historyRibbon .history-chip:last').remove();
                    }

                    // Settle Game End via Backend if bet was placed
                    if (gameLogId) {
                        $.ajax({
                            url: gameEndUrl,
                            type: 'POST',
                            dataType: 'json',
                            headers: { "X-CSRF-TOKEN": csrfToken },
                            data: {
                                _token: csrfToken,
                                game_log_id: gameLogId,
                                winNumber: finalNum
                            },
                            success: function(endRes) {
                                if (endRes.win_status === 1) {
                                    if (typeof playAudio === 'function') playAudio(audioAssetPath, 'win.wav');
                                    notify('success', '🎉 YOU WON! Winning Number: ' + finalNum + ' (' + winColor.toUpperCase() + ')');
                                } else {
                                    if (typeof playAudio === 'function') playAudio(audioAssetPath, 'lose.wav');
                                    notify('info', 'Winning Number: ' + finalNum + ' (' + winColor.toUpperCase() + ')');
                                }

                                if (endRes.bal) {
                                    $('.user-balance').text(endRes.bal);
                                }
                                resetForNextRound();
                            },
                            error: function() {
                                resetForNextRound();
                            }
                        });
                    } else {
                        // Demo auto spin result notification
                        notify('info', 'Round Finished! Winning Number: ' + finalNum + ' (' + winColor.toUpperCase() + ')');
                        resetForNextRound();
                    }
                });
            }

            function resetForNextRound() {
                setTimeout(function() {
                    isWheelSpinning = false;
                    luxuryWheel.reset();
                    placedBets = {};
                    betHistoryStack = [];
                    renderTableChips();
                    startRoundLoop();
                }, 3500);
            }

            // Start continuous live casino loop on page load!
            startRoundLoop();

        })(jQuery);
    </script>
@endpush

@push('style')
    <style>
        /* Top Live Bar */
        .roulette-top-bar {
            background: linear-gradient(90deg, #121927, #1c2638);
            border: 1px solid rgba(255, 193, 7, 0.25);
        }
        .history-chip {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            font-size: 11px;
            font-weight: bold;
            color: #fff;
            border: 1px solid rgba(255,255,255,0.4);
            box-shadow: 0 2px 4px rgba(0,0,0,0.4);
            flex-shrink: 0;
        }

        /* 3D Casino Chips Tray */
        .chips-container {
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .chip-item {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            position: relative;
            user-select: none;
            transition: all 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5), inset 0 2px 4px rgba(255, 255, 255, 0.4);
            border: 3px dashed rgba(255, 255, 255, 0.8);
        }
        .chip-item:hover {
            transform: translateY(-4px) scale(1.08);
        }
        .chip-item.active {
            transform: translateY(-8px) scale(1.15);
            box-shadow: 0 0 0 3px #ffc107, 0 8px 16px rgba(255, 193, 7, 0.6);
        }
        .chip-val {
            font-size: 11px;
            font-weight: 900;
            color: #fff;
            text-shadow: 0 1px 2px rgba(0,0,0,0.8);
        }

        /* Authentic Chip Colors */
        .chip-10  { background: radial-gradient(circle, #00b4d8 60%, #0077b6); }
        .chip-20  { background: radial-gradient(circle, #2ec4b6 60%, #1b9aaa); }
        .chip-50  { background: radial-gradient(circle, #ff9f1c 60%, #e76f51); }
        .chip-100 { background: radial-gradient(circle, #e63946 60%, #b5179e); }
        .chip-200 { background: radial-gradient(circle, #7209b7 60%, #3f37c9); }
        .chip-500 { background: radial-gradient(circle, #ffd166 60%, #b4903a); border-color: #000; }
        .chip-500 .chip-val { color: #000; }

        /* Table Mini Chips on Placed Spots */
        .spot-cell {
            position: relative !important;
            cursor: pointer;
            transition: all 0.15s ease-in-out;
            user-select: none;
        }
        .spot-cell.has-chips {
            box-shadow: inset 0 0 0 2px #ffc107, 0 0 10px rgba(255, 193, 7, 0.8) !important;
        }
        .chip-badge {
            display: none;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 10;
            pointer-events: none;
        }
        .table-mini-chip {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            background: radial-gradient(circle, #ffc107 50%, #d39e00);
            color: #000;
            font-weight: 900;
            font-size: 9px;
            padding: 2px 4px;
            border-radius: 10px;
            border: 1px solid #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.8);
            line-height: 1;
            white-space: nowrap;
        }

        .table-locked {
            opacity: 0.65;
            pointer-events: none !important;
            cursor: not-allowed !important;
        }

        /* Zero Horizontal Scroll Mobile Table */
        @media (max-width: 991px) {
            .game-wrapper > div:nth-child(1) { order: 1 !important; }
            .game-wrapper > div:nth-child(2) { order: 2 !important; }
            .game-wrapper > div:nth-child(3) { order: 3 !important; }
            .game-wrapper > div:nth-child(4) { order: 4 !important; }

            /* Canvas wheel container */
            .plate {
                transform: scale(0.78) !important;
                margin: -30px auto !important;
            }

            .roulette-wrapper {
                width: 100% !important;
                max-width: 100% !important;
                overflow: hidden !important;
                padding: 1px !important;
                margin: 0 auto !important;
                height: auto !important;
            }
            .roulette-wrapper table {
                width: 100% !important;
                max-width: 100% !important;
                table-layout: fixed !important;
                border-collapse: separate !important;
                border-spacing: 1.5px !important;
                margin: 0 auto !important;
            }
            .roulette-wrapper table tr td {
                padding: 0 !important;
                height: 32px !important;
                max-height: 32px !important;
                line-height: 32px !important;
                font-size: 11px !important;
                border-radius: 3px !important;
                border: 1px solid rgba(255, 255, 255, 0.2) !important;
                text-align: center !important;
            }
            .roulette-wrapper table .zero {
                width: 7.5% !important;
            }
            .roulette-wrapper table .zero .number-item {
                line-height: 98px !important;
                font-size: 12px !important;
            }
            .roulette-wrapper table .number-item {
                font-size: 10.5px !important;
                font-weight: 700 !important;
                line-height: 32px !important;
                display: block !important;
            }
            .roulette-wrapper table .btn-color {
                font-size: 8.5px !important;
                font-weight: 700 !important;
                padding: 0 !important;
                height: 26px !important;
                line-height: 26px !important;
                width: 100% !important;
                border: none !important;
                background: transparent !important;
                color: #fff !important;
            }
        }

        @media (max-width: 480px) {
            .chip-item {
                width: 38px;
                height: 38px;
            }
            .chip-val {
                font-size: 10px;
            }
            /* Clean Canvas Wheel Layout */
            .roulette-canvas-wrapper {
            position: relative;
            width: 100%;
            margin: 0 auto;
            display: flex;
            justify-content: center;
            align-items: center;
        }
            .roulette-wrapper table tr td {
                height: 29px !important;
                max-height: 29px !important;
                line-height: 29px !important;
            }
            .roulette-wrapper table .number-item {
                font-size: 10px !important;
                line-height: 29px !important;
            }
            .roulette-wrapper table .zero .number-item {
                line-height: 89px !important;
            }
            .roulette-wrapper table .btn-color {
                font-size: 8px !important;
                height: 22px !important;
                line-height: 22px !important;
            }
        }
    </style>
@endpush
