@extends('Template::layouts.master')
@section('content')
    <div class="row gy-4 gx-lg-4 align-items-center game-wrapper">
        <div class="col-lg-6">
            <div class="headtail-body">
                @include('Template::partials.game_shape')
                <div class="headtail-body__flip">
                    <div class="roulette-game-spin">
                        <div class="plate" id="plate">
                            <ul class="inner">
                                <li class="number"><label><input name="pit" type="radio" value="32" /><span class="pit">32</span></label></li>
                                <li class="number"><label><input name="pit" type="radio" value="15" /><span class="pit">15</span></label></li>
                                <li class="number"><label><input name="pit" type="radio" value="19" /><span class="pit">19</span></label></li>
                                <li class="number"><label><input name="pit" type="radio" value="4" /><span class="pit">4</span></label></li>
                                <li class="number"><label><input name="pit" type="radio" value="21" /><span class="pit">21</span></label></li>
                                <li class="number"><label><input name="pit" type="radio" value="2" /><span class="pit">2</span></label></li>
                                <li class="number"><label><input name="pit" type="radio" value="25" /><span class="pit">25</span></label></li>
                                <li class="number"><label><input name="pit" type="radio" value="17" /><span class="pit">17</span></label></li>
                                <li class="number"><label><input name="pit" type="radio" value="34" /><span class="pit">34</span></label></li>
                                <li class="number"><label><input name="pit" type="radio" value="6" /><span class="pit">6</span></label></li>
                                <li class="number"><label><input name="pit" type="radio" value="27" /><span class="pit">27</span></label></li>
                                <li class="number"><label><input name="pit" type="radio" value="13" /><span class="pit">13</span></label></li>
                                <li class="number"><label><input name="pit" type="radio" value="36" /><span class="pit">36</span></label></li>
                                <li class="number"><label><input name="pit" type="radio" value="11" /><span class="pit">11</span></label></li>
                                <li class="number"><label><input name="pit" type="radio" value="30" /><span class="pit">30</span></label></li>
                                <li class="number"><label><input name="pit" type="radio" value="8" /><span class="pit">8</span></label></li>
                                <li class="number"><label><input name="pit" type="radio" value="23" /><span class="pit">23</span></label></li>
                                <li class="number"><label><input name="pit" type="radio" value="10" /><span class="pit">10</span></label></li>
                                <li class="number"><label><input name="pit" type="radio" value="5" /><span class="pit">5</span></label></li>
                                <li class="number"><label><input name="pit" type="radio" value="24" /><span class="pit">24</span></label></li>
                                <li class="number"><label><input name="pit" type="radio" value="16" /><span class="pit">16</span></label></li>
                                <li class="number"><label><input name="pit" type="radio" value="33" /><span class="pit">33</span></label></li>
                                <li class="number"><label><input name="pit" type="radio" value="1" /><span class="pit">1</span></label></li>
                                <li class="number"><label><input name="pit" type="radio" value="20" /><span class="pit">20</span></label></li>
                                <li class="number"><label><input name="pit" type="radio" value="14" /><span class="pit">14</span></label></li>
                                <li class="number"><label><input name="pit" type="radio" value="31" /><span class="pit">31</span></label></li>
                                <li class="number"><label><input name="pit" type="radio" value="9" /><span class="pit">9</span></label></li>
                                <li class="number"><label><input name="pit" type="radio" value="22" /><span class="pit">22</span></label></li>
                                <li class="number"><label><input name="pit" type="radio" value="18" /><span class="pit">18</span></label></li>
                                <li class="number"><label><input name="pit" type="radio" value="29" /><span class="pit">29</span></label></li>
                                <li class="number"><label><input name="pit" type="radio" value="7" /><span class="pit">7</span></label></li>
                                <li class="number"><label><input name="pit" type="radio" value="28" /><span class="pit">28</span></label></li>
                                <li class="number"><label><input name="pit" type="radio" value="12" /><span class="pit">12</span></label></li>
                                <li class="number"><label><input name="pit" type="radio" value="35" /><span class="pit">35</span></label></li>
                                <li class="number"><label><input name="pit" type="radio" value="3" /><span class="pit">3</span></label></li>
                                <li class="number"><label><input name="pit" type="radio" value="26" /><span class="pit">26</span></label></li>
                                <li class="number"><label><input name="pit" type="radio" value="0" /><span class="pit">0</span></label></li>
                            </ul>
                            <div class="data">
                                <div class="data-inner">
                                    <div class="mask"></div>
                                    <div class="result">
                                        <div class="result-number">00</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="headtail-wrapper game-contet__sm">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <h4 class="game-contet-title mb-0">
                        {{ $isDemo ? trans('Demo Balance:') : trans('Real Balance:') }}
                        <span class="text user-balance">{{ showAmount($balance, currencyFormat: false) }}</span>
                        {{ __(gs('cur_text')) }}
                    </h4>
                    @if($isDemo)
                        <a href="{{ route('user.play.game', $game->alias) }}" class="btn btn-sm btn--success py-1 px-3 text-white" style="border-radius: 6px; font-size: 13px;">
                            <i class="fas fa-coins me-1"></i> @lang('Real Play (10,000 Balance)')
                        </a>
                    @else
                        <a href="{{ route('user.play.game', [$game->alias, 'demo']) }}" class="btn btn-sm btn--secondary py-1 px-3 text-white" style="border-radius: 6px; font-size: 13px;">
                            <i class="fas fa-gamepad me-1"></i> @lang('Demo Mode')
                        </a>
                    @endif
                </div>

                <form id="game" method="post">
                    @csrf
                    <div class="form-group mb-3">
                        <div class="input-group">
                            <span class="input-group-text">{{ gs('cur_sym') }}</span>
                            <input type="number" step="any" class="form-control form--control" placeholder="@lang('Enter amount')" name="invest" value="{{ old('invest') }}">
                            <button type="button" class="input-group-text minmax-btn minBtn">@lang('Min')</button>
                            <button type="button" class="input-group-text minmax-btn maxBtn">@lang('Max')</button>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2 flex-wrap gap-1">
                            <small class="fw-light input-inner-note text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                @lang('Limit'): {{ showAmount($game->min_limit) }} - {{ showAmount($game->max_limit) }}
                            </small>
                            <div class="win-bonus text--base fw-bold"></div>
                        </div>
                    </div>

                    <input name="choose" type="hidden">

                    <div class="form-submit game-playbtn mb-2">
                        <button type="button" class="btn btn--gradient play-btn w-100 py-2 fs-5">
                            <i class="fas fa-play me-2"></i>@lang('Play Now')
                        </button>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-2 w-100">
                        <button type="button" class="btn btn-sm btn-outline-danger clear-bets-btn px-2 py-1" style="font-size: 12px;">
                            <i class="fas fa-trash-alt me-1"></i> @lang('Clear All Bets')
                        </button>
                        <button type="button" class="d-block text-white text-center btn btn-sm btn-link p-0 text-decoration-none" data-bs-toggle="modal"
                            data-bs-target="#exampleModalCenter">
                            <i class="fas fa-info-circle me-1"></i> @lang('Game Instruction')
                        </button>
                        <button type="button" class="sound--btn audioBtn">
                            <i class="fas fa-volume-up"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="game--card p-2 p-md-3">
                <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                    <span class="text-warning fw-bold" style="font-size: 12px;">
                        <i class="fas fa-hand-pointer me-1"></i> @lang('Multi-Bet Table') <small class="text-white fw-normal">(@lang('Tap numbers or groups to toggle bets'))</small>
                    </span>
                    <button type="button" class="btn btn-sm btn-outline-warning clear-bets-btn py-0 px-2" style="font-size: 11px;">
                        @lang('Reset Board')
                    </button>
                </div>
                <div class="all-number d-block roulette-wrapper game-details-right">
                    <table>
                        <tbody>
                            <tr class="numbers">
                                <td class="zero bg-transparent" rowspan="3"> <span class="number-item">0</span></td>
                                <td class="bg-danger redEl oneToTwEl oneToEtEl oddEl"> <span class="number-item">3</span></td>
                                <td class="bg-dark blackEl oneToTwEl oneToEtEl evenEl"> <span class="number-item">6</span></td>
                                <td class="bg-danger redEl oneToTwEl oneToEtEl oddEl"> <span class="number-item">9</span></td>
                                <td class="bg-danger redEl oneToTwEl oneToEtEl evenEl"> <span class="number-item">12</span></td>
                                <td class="bg-dark blackEl thrtToTfEl oneToEtEl oddEl"> <span class="number-item">15</span></td>
                                <td class="bg-danger redEl thrtToTfEl oneToEtEl evenEl"> <span class="number-item">18</span></td>
                                <td class="bg-danger redEl thrtToTfEl nineteenTtsixEl oddEl"> <span class="number-item">21</span></td>
                                <td class="bg-dark blackEl thrtToTfEl nineteenTtsixEl evenEl"> <span class="number-item">24</span></td>
                                <td class="bg-danger redEl twfToTsEl nineteenTtsixEl oddEl"> <span class="number-item">27</span></td>
                                <td class="bg-danger redEl twfToTsEl nineteenTtsixEl evenEl"> <span class="number-item">30</span></td>
                                <td class="bg-dark blackEl twfToTsEl nineteenTtsixEl oddEl"> <span class="number-item">33</span></td>
                                <td class="bg-danger redEl twfToTsEl nineteenTtsixEl evenEl"> <span class="number-item">36</span></td>
                                <td class="twByOne1 p-0">
                                    <button class="btn-color" type="button">2:1</button>
                                </td>
                            </tr>
                            <tr class="numbers">
                                <td class="bg-dark blackEl oneToTwEl oneToEtEl evenEl"> <span class="number-item">2</span></td>
                                <td class="bg-danger redEl oneToTwEl oneToEtEl oddEl"> <span class="number-item">5</span></td>
                                <td class="bg-dark blackEl oneToTwEl oneToEtEl evenEl"> <span class="number-item">8</span></td>
                                <td class="bg-dark blackEl oneToTwEl oneToEtEl oddEl"> <span class="number-item">11</span></td>
                                <td class="bg-danger redEl thrtToTfEl oneToEtEl evenEl"> <span class="number-item">14</span></td>
                                <td class="bg-dark blackEl thrtToTfEl oneToEtEl oddEl"> <span class="number-item">17</span></td>
                                <td class="bg-dark blackEl thrtToTfEl nineteenTtsixEl evenEl"><span class="number-item">20</span></td>
                                <td class="bg-danger redEl thrtToTfEl nineteenTtsixEl oddEl"> <span class="number-item">23</span></td>
                                <td class="bg-dark blackEl twfToTsEl nineteenTtsixEl evenEl"> <span class="number-item">26</span></td>
                                <td class="bg-dark blackEl twfToTsEl nineteenTtsixEl oddEl"> <span class="number-item">29</span></td>
                                <td class="bg-danger redEl twfToTsEl nineteenTtsixEl evenEl"> <span class="number-item">32</span></td>
                                <td class="bg-dark blackEl twfToTsEl nineteenTtsixEl oddEl"> <span class="number-item">35</span></td>
                                <td class="twByOne2 p-0">
                                    <button class="btn-color" type="button">2:1</button>
                                </td>
                            </tr>
                            <tr class="numbers">
                                <td class="bg-danger redEl oneToTwEl oneToEtEl oddEl"> <span class="number-item">1</span></td>
                                <td class="bg-dark blackEl oneToTwEl oneToEtEl evenEl"> <span class="number-item">4</span></td>
                                <td class="bg-danger redEl oneToTwEl oneToEtEl oddEl"> <span class="number-item">7</span></td>
                                <td class="bg-dark blackEl oneToTwEl oneToEtEl evenEl"> <span class="number-item">10</span></td>
                                <td class="bg-dark blackEl thrtToTfEl oneToEtEl oddEl"> <span class="number-item">13</span></td>
                                <td class="bg-danger redEl thrtToTfEl oneToEtEl evenEl"> <span class="number-item">16</span></td>
                                <td class="bg-danger redEl thrtToTfEl nineteenTtsixEl oddEl"> <span class="number-item">19</span></td>
                                <td class="bg-dark blackEl thrtToTfEl nineteenTtsixEl evenEl"> <span class="number-item">22</span></td>
                                <td class="bg-danger redEl twfToTsEl nineteenTtsixEl oddEl"> <span class="number-item">25</span></td>
                                <td class="bg-dark blackEl twfToTsEl nineteenTtsixEl evenEl"> <span class="number-item">28</span></td>
                                <td class="bg-dark blackEl twfToTsEl nineteenTtsixEl oddEl"> <span class="number-item">31</span></td>
                                <td class="bg-danger redEl twfToTsEl nineteenTtsixEl evenEl"> <span class="number-item">34</span></td>
                                <td class="twByOne3 p-0">
                                    <button class="btn-color" type="button">2:1</button>
                                </td>
                            </tr>
                            <tr>
                                <td class="border-0"></td>
                                <td class="oneToTw text-center" colspan="4">
                                    <button class="w-100 btn-color" type="button"><span>1 to 12</span></button>
                                </td>
                                <td class="thrtToTf text-center" colspan="4">
                                    <button class="w-100 btn-color" type="button"><span>13 to 24</span></button>
                                </td>
                                <td class="twfToTs text-center" colspan="4">
                                    <button class="w-100 btn-color" type="button"><span>25 to 36</span></button>
                                </td>
                                <td class="border-0"></td>
                            </tr>
                            <tr>
                                <td class="border-0"></td>
                                <td class="oneToEt text-center" colspan="2">
                                    <button class="w-100 btn-color" type="button"><span>1 to 18</span></button>
                                </td>
                                <td class="even text-center" colspan="2">
                                    <button class="w-100 btn-color" type="button"><span>@lang('Even')</span></button>
                                </td>
                                <td class="bg-danger red text-center" colspan="2">
                                    <button class="w-100 btn-color" type="button"><span>@lang('Red')</span></button>
                                </td>
                                <td class="bg-dark black text-center" colspan="2">
                                    <button class="w-100 btn-color" type="button"><span>@lang('Black')</span></button>
                                </td>
                                <td class="odd text-center" colspan="2">
                                    <button class="w-100 btn-color" type="button"><span>@lang('Odd')</span></button>
                                </td>
                                <td class="nineteenTtsix text-center" colspan="2">
                                    <button class="w-100 btn-color" type="button"><span>19 to 36</span></button>
                                </td>
                                <td class="border-0"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal custom--modal fade" id="exampleModalCenter">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content section--bg">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">@lang('Game Rule')</h5>
                    <span class="close" data-bs-dismiss="modal" type="button" aria-label="Close">
                        <i class="las la-times"></i>
                    </span>
                </div>
                <div class="modal-body">
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
    <script src="{{ asset('assets/global/js/game/roulette.js') }}"></script>
    <script src="{{ asset('assets/global/js/game/roulette-wheel.js') }}"></script>
@endpush

@push('script')
    <script>
        (function($) {
            'use strict';
            var investField = $("[name=invest]");
            var minLimit = Number("{{ $game->min_limit }}");
            var maxLimit = Number("{{ $game->max_limit }}");
            var currency = "{{ gs('cur_text') }}";
            var timer = 9000;
            var investUrl = "{{ route('user.play.invest', ['roulette', @$isDemo]) }}";
            var gameEndUrl = "{{ route('user.play.end', ['roulette', @$isDemo]) }}";
            var audioAssetPath = `{{ asset('assets/audio') }}`;
            var running = 0;
            var redNumbers = [32, 19, 21, 25, 34, 27, 36, 30, 23, 5, 16, 1, 14, 9, 18, 7, 12, 3];

            // Expose updateBonusDisplay globally for roulette.js
            window.updateBonusDisplay = function() {
                var count = selectedBets.length;
                var invest = parseFloat($('input[name=invest]').val()) || 0;
                if (count > 0 && invest > 0) {
                    var multiplier = (36 / count).toFixed(2);
                    var winAmo = (invest * (36 / count)).toFixed(2);
                    $('.win-bonus').html(
                        `<span class="badge bg-warning text-dark me-1" style="font-size: 11px;">${count} Bets</span>` +
                        `<span style="font-size: 12px;"><strong>${multiplier}x</strong> | Win: <strong>${winAmo} ${currency}</strong></span>`
                    );
                } else if (count > 0) {
                    var multiplier = (36 / count).toFixed(2);
                    $('.win-bonus').html(
                        `<span class="badge bg-warning text-dark me-1" style="font-size: 11px;">${count} Bets</span>` +
                        `<span style="font-size: 12px;"><strong>${multiplier}x</strong> Multiplier</span>`
                    );
                } else {
                    $('.win-bonus').html(`<span class="text-muted" style="font-size: 11px;">Select numbers on board</span>`);
                }
            };

            $(".minBtn").on('click', function(e) {
                if (typeof playAudio === 'function') playAudio(audioAssetPath, "click.mp3");
                investField.val(minLimit);
                window.updateBonusDisplay();
            });

            $(".maxBtn").on('click', function(e) {
                if (typeof playAudio === 'function') playAudio(audioAssetPath, "click.mp3");
                investField.val(maxLimit);
                window.updateBonusDisplay();
            });

            $('[name=invest]').on('input keyup change', function(e) {
                window.updateBonusDisplay();
            });

            // Play button submission
            $('.play-btn').on('click', function(e) {
                e.preventDefault();
                var investVal = parseFloat($('input[name=invest]').val());
                var chooseVal = $('input[name=choose]').val();

                if (!investVal || investVal <= 0) {
                    notify('error', 'Please enter a valid investment amount');
                    return;
                }

                if (investVal < minLimit) {
                    notify('error', 'Minimum invest limit is ' + minLimit + ' ' + currency);
                    return;
                }

                if (investVal > maxLimit) {
                    notify('error', 'Maximum invest limit is ' + maxLimit + ' ' + currency);
                    return;
                }

                if (!chooseVal || chooseVal.trim() === '') {
                    notify('error', 'Please select at least one number or option on the betting table');
                    return;
                }

                if (running === 1) {
                    notify('error', 'A spin is already in progress, please wait');
                    return false;
                }

                running = 1;
                $('.play-btn').html('<i class="fas fa-spinner fa-spin me-2"></i> Spinning...').prop('disabled', true);

                $.ajaxSetup({
                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    }
                });

                var postData = {
                    invest: investVal,
                    choose: chooseVal,
                };

                $.post(investUrl, postData, function(response) {
                    if (response.error) {
                        running = 0;
                        notify('error', response.error);
                        $('.play-btn').html('<i class="fas fa-play me-2"></i> Play Now').prop('disabled', false);
                        return false;
                    }

                    if (response.errors) {
                        running = 0;
                        var msg = Array.isArray(response.errors) ? response.errors.join('<br>') : response.errors;
                        notify('error', msg);
                        $('.play-btn').html('<i class="fas fa-play me-2"></i> Play Now').prop('disabled', false);
                        return false;
                    }

                    if (response.balance) {
                        $('.user-balance').text(response.balance);
                    }

                    var winningNumber = parseInt(response.result, 10);
                    var gameLogId = response.game_log_id;

                    // Trigger sound and wheel
                    $('.roulette-game-spin').addClass('spin-active');
                    if (typeof playAudio === 'function') {
                        playAudio(audioAssetPath, "spin.mp3");
                    }

                    // Reset and apply spinto animation
                    var $inner = $('.inner');
                    $inner.removeAttr('data-spinto').removeClass('rest');
                    if ($inner[0]) {
                        void $inner[0].offsetHeight;
                    }
                    $inner.attr('data-spinto', winningNumber);
                    $inner.find('input[value="' + winningNumber + '"]').prop('checked', true);

                    $('.data').removeClass('reveal');
                    $('.mask').text('');

                    setTimeout(function() {
                        $('.mask').text('');
                    }, timer / 2);

                    // When 9s timer completes
                    setTimeout(function() {
                        var winColor = 'black';
                        if (redNumbers.indexOf(winningNumber) !== -1) {
                            winColor = 'red';
                        } else if (winningNumber === 0) {
                            winColor = 'green';
                        }

                        $('.result-number').text(winningNumber);
                        $('.result-color').text(winColor);
                        $('.result').css({'background-color': winColor});
                        $('.data').addClass('reveal');
                        $inner.addClass('rest');

                        var resItem = '<li class="previous-result color-' + winColor + '"><span class="previous-number">' + winningNumber + '</span><span class="previous-color">' + winColor + '</span></li>';
                        $('.previous-list').prepend(resItem);

                        var endData = {
                            game_log_id: gameLogId,
                            winNumber: winningNumber
                        };

                        $.post(gameEndUrl, endData, function(endResponse) {
                            if (typeof audio !== 'undefined' && audio && typeof audio.pause === 'function') {
                                try { audio.pause(); } catch(e) {}
                            }

                            if (endResponse.error) {
                                if (typeof playAudio === 'function') playAudio(audioAssetPath, "lose.wav");
                                notify('error', endResponse.error);
                            } else if (endResponse.errors) {
                                if (typeof playAudio === 'function') playAudio(audioAssetPath, "lose.wav");
                                var endMsg = Array.isArray(endResponse.errors) ? endResponse.errors.join('<br>') : endResponse.errors;
                                notify('error', endMsg);
                            } else {
                                $(".win-loss-popup").addClass("active");
                                $(".win-loss-popup__body").find("img").addClass("d-none");
                                if (endResponse.win_status === 1) {
                                    if (typeof playAudio === 'function') playAudio(audioAssetPath, 'win.wav');
                                    $(".win-loss-popup__body").find(".win").removeClass("d-none");
                                } else {
                                    if (typeof playAudio === 'function') playAudio(audioAssetPath, 'lose.wav');
                                    $(".win-loss-popup__body").find(".lose").removeClass("d-none");
                                }
                                $(".win-loss-popup__footer").find(".data-result").text(winningNumber);
                                if (endResponse.bal) {
                                    $('.user-balance').text(endResponse.bal);
                                }
                            }

                            $('.roulette-game-spin').removeClass('spin-active');
                            $('.play-btn').html('<i class="fas fa-play me-2"></i> Play Now').prop('disabled', false);
                            running = 0;
                        }).fail(function() {
                            running = 0;
                            $('.play-btn').html('<i class="fas fa-play me-2"></i> Play Now').prop('disabled', false);
                        });

                    }, timer);

                }).fail(function(xhr) {
                    running = 0;
                    $('.play-btn').html('<i class="fas fa-play me-2"></i> Play Now').prop('disabled', false);
                    var errText = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error processing request';
                    notify('error', errText);
                });
            });

            // Initial bonus display
            window.updateBonusDisplay();
        })(jQuery);
    </script>
@endpush

@push('style')
    <style>
        .selected {
            color: #000 !important;
            background: linear-gradient(#ffe066, #ffc107) !important;
            box-shadow: 0 0 0 2px #fff, 0 0 10px #ffc107 !important;
            font-weight: 900 !important;
        }
        .selected .number-item,
        .selected span,
        .selected button {
            color: #000 !important;
            font-weight: 900 !important;
        }
        .__select {
            outline: 2px solid #ffeb3b !important;
        }

        /* Responsive Mobile Layout */
        @media (max-width: 991px) {
            .game-wrapper {
                display: flex !important;
                flex-direction: column !important;
                row-gap: 12px !important;
            }
            .game-wrapper > div:nth-child(1) {
                order: 1 !important;
                margin-bottom: 0 !important;
                width: 100% !important;
            }
            .game-wrapper > div:nth-child(2) {
                order: 2 !important;
                margin-bottom: 0 !important;
                width: 100% !important;
            }
            .game-wrapper > div:nth-child(3) {
                order: 3 !important;
                width: 100% !important;
            }
            .headtail-body {
                padding: 12px 8px !important;
                min-height: auto !important;
                border-radius: 12px !important;
            }
            .headtail-wrapper {
                padding: 12px 10px !important;
                border-radius: 12px !important;
            }
            .roulette-game-spin {
                margin: 0 auto !important;
                display: flex !important;
                justify-content: center !important;
                align-items: center !important;
                height: 240px !important;
                overflow: hidden !important;
            }
            .plate {
                transform: scale(0.82) !important;
                transform-origin: center center !important;
                margin: -25px auto !important;
            }

            /* ZERO HORIZONTAL SCROLLING TABLE */
            .roulette-wrapper {
                width: 100% !important;
                max-width: 100% !important;
                overflow: hidden !important;
                padding: 2px !important;
                margin: 0 auto !important;
                box-sizing: border-box !important;
                height: auto !important;
            }
            .roulette-wrapper table {
                width: 100% !important;
                max-width: 100% !important;
                min-width: 0 !important;
                table-layout: fixed !important;
                border-collapse: separate !important;
                border-spacing: 1.5px !important;
                margin: 0 auto !important;
            }
            .roulette-wrapper table tr td {
                padding: 0 !important;
                height: 34px !important;
                max-height: 34px !important;
                font-size: 11px !important;
                line-height: 34px !important;
                border-radius: 3px !important;
                border: 1px solid rgba(255, 255, 255, 0.25) !important;
                box-sizing: border-box !important;
                cursor: pointer !important;
                text-align: center !important;
            }
            .roulette-wrapper table .zero {
                width: 7.6% !important;
            }
            .roulette-wrapper table .zero .number-item {
                line-height: 104px !important;
                font-size: 13px !important;
                display: block !important;
            }
            .roulette-wrapper table .number-item {
                font-size: 11px !important;
                font-weight: 700 !important;
                line-height: 34px !important;
                display: block !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .roulette-wrapper table .btn-color {
                font-size: 9px !important;
                font-weight: 700 !important;
                padding: 0 !important;
                height: 28px !important;
                line-height: 28px !important;
                width: 100% !important;
                display: block !important;
                background: transparent !important;
                border: none !important;
                color: #fff !important;
            }
        }

        @media (max-width: 480px) {
            .plate {
                transform: scale(0.72) !important;
                margin: -35px auto !important;
            }
            .roulette-game-spin {
                height: 215px !important;
            }
            .roulette-wrapper table tr td {
                height: 30px !important;
                max-height: 30px !important;
                line-height: 30px !important;
            }
            .roulette-wrapper table .number-item {
                font-size: 10px !important;
                line-height: 30px !important;
            }
            .roulette-wrapper table .zero .number-item {
                line-height: 92px !important;
                font-size: 12px !important;
            }
            .roulette-wrapper table .btn-color {
                font-size: 8px !important;
                height: 24px !important;
                line-height: 24px !important;
            }
        }
    </style>
@endpush
