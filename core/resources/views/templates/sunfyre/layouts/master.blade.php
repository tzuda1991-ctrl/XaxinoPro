@extends('Template::layouts.app')
@section('app')
    @include('Template::partials.user_header')
    @php
        $isGamePage = request()->routeIs('user.play.game');
    @endphp
    @if (!request()->routeIs('home') && !$isGamePage)
        @include('Template::partials.breadcrumb')
    @endif
    <section class="{{ $isGamePage ? 'py-2 py-lg-4 game-page-section' : 'py-100' }}">
        <div class="container">
            @if ($isGamePage && auth()->check())
                @php
                    $currAlias = request()->route('alias') ?? (@$game->alias ?? '');
                    $currIsDemo = request()->route('demo') === 'demo' || !empty($isDemo);
                    $authUser = auth()->user();
                @endphp
                <div class="casino-compact-topbar d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2 px-2 py-2 rounded-3">
                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('user.home', [], false) }}" class="btn btn-sm btn-outline--base py-1 px-2" style="font-size: 12px; border-radius: 6px;">
                            <i class="las la-arrow-left"></i> @lang('Lobby')
                        </a>
                        <span class="fw-bold text-white" style="font-size: 14px;">{{ __(@$game->name ?? $pageTitle) }}</span>
                    </div>
                    <div class="d-flex align-items-center flex-wrap gap-1">
                        @if ($currAlias)
                            <a href="{{ route('user.play.game', [$currAlias], false) }}"
                               class="wallet-mode-pill {{ !$currIsDemo ? 'active-real' : '' }}">
                                <i class="las la-wallet"></i>
                                <span>@lang('Real'): <strong>{{ showAmount($authUser->balance) }}</strong></span>
                            </a>
                            <a href="{{ route('user.play.game', [$currAlias, 'demo'], false) }}"
                               class="wallet-mode-pill {{ $currIsDemo ? 'active-demo' : '' }}">
                                <i class="las la-gamepad"></i>
                                <span>@lang('Demo'): <strong>{{ showAmount($authUser->demo_balance) }}</strong></span>
                            </a>
                        @endif
                        <a href="{{ route('user.deposit.index', [], false) }}" class="btn btn-sm btn--base py-1 px-2 fw-bold" style="font-size: 11px; border-radius: 6px;">
                            <i class="las la-plus-circle"></i> @lang('Deposit')
                        </a>
                    </div>
                </div>
            @endif
            @yield('content')
        </div>
    </section>
    @include('Template::partials.footer')
@endsection

@push('style')
    <style>
        /* Compact Top Bar on Game Pages */
        .casino-compact-topbar {
            background: linear-gradient(90deg, #111827, #1e293b);
            border: 1px solid rgba(255, 193, 7, 0.28);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.35);
        }
        .wallet-mode-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 9px;
            border-radius: 6px;
            font-size: 11.5px;
            color: #cbd5e1;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.14);
            text-decoration: none;
            transition: all 0.15s ease;
        }
        .wallet-mode-pill:hover {
            color: #fff;
            border-color: hsl(var(--base));
        }
        .wallet-mode-pill.active-real {
            background: rgba(16, 185, 129, 0.2);
            border-color: #10b981;
            color: #ecfdf5;
            box-shadow: 0 0 8px rgba(16, 185, 129, 0.35);
        }
        .wallet-mode-pill.active-demo {
            background: rgba(245, 158, 11, 0.2);
            border-color: #f59e0b;
            color: #fffbeb;
            box-shadow: 0 0 8px rgba(245, 158, 11, 0.35);
        }

        /* Completely eliminate the empty yellow SVG game_shape frame across all games */
        .headtail-body__shape {
            display: none !important;
        }
        .game-page-section .headtail-body {
            position: relative !important;
            padding: 18px 14px !important;
            background: radial-gradient(circle at center, #162032 0%, #0b111c 100%) !important;
            border: 1px solid rgba(255, 193, 7, 0.25) !important;
            border-top: 3px solid hsl(var(--base)) !important;
            border-radius: 14px !important;
            min-height: 220px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.45) !important;
        }
        .game-page-section .headtail-body__flip {
            position: relative !important;
            top: auto !important;
            left: auto !important;
            transform: none !important;
            width: 100% !important;
            height: auto !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        /* Universal Quick Casino Chip Bar for all games with invest input */
        .universal-chip-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 6px;
            background: rgba(0, 0, 0, 0.35);
            border: 1px solid rgba(255, 193, 7, 0.22);
            border-radius: 10px;
            padding: 8px 10px;
            margin-top: 8px;
            margin-bottom: 10px;
        }
        .u-chip-btn {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 900;
            color: #fff;
            border: 2.5px dashed rgba(255, 255, 255, 0.85);
            cursor: pointer;
            user-select: none;
            box-shadow: 0 3px 6px rgba(0,0,0,0.45);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
            padding: 0;
        }
        .u-chip-btn:hover, .u-chip-btn:active {
            transform: translateY(-3px) scale(1.08);
            box-shadow: 0 0 0 2px #ffc107, 0 5px 12px rgba(255,193,7,0.5);
        }
        .u-chip-10  { background: radial-gradient(circle, #00b4d8 60%, #0077b6); }
        .u-chip-20  { background: radial-gradient(circle, #2ec4b6 60%, #1b9aaa); }
        .u-chip-50  { background: radial-gradient(circle, #ff9f1c 60%, #e76f51); }
        .u-chip-100 { background: radial-gradient(circle, #e63946 60%, #b5179e); }
        .u-chip-200 { background: radial-gradient(circle, #7209b7 60%, #3f37c9); }
        .u-chip-500 { background: radial-gradient(circle, #ffd166 60%, #b4903a); color: #000; border-color: #111; }
        .u-chip-clear {
            border-radius: 6px;
            padding: 5px 8px;
            font-size: 11px;
            font-weight: 700;
            background: rgba(220, 53, 69, 0.2);
            border: 1px solid #dc3545;
            color: #ff8787;
            cursor: pointer;
        }

        /* Highlight selected game choice box (Andar/Bahar, Head/Tail, Red/Black, Rock/Paper/Scissors) */
        .game-select-box.op,
        .game-select-box.active,
        .single-select.op,
        .single-select.active {
            border: 2px solid #ffc107 !important;
            box-shadow: 0 0 14px rgba(255, 193, 7, 0.55) !important;
            transform: scale(1.03);
        }

        /* Mobile Compact Game Layout so users never have to scroll excessively */
        @media (max-width: 767px) {
            .game-page-section .row.gy-5 {
                --bs-gutter-y: 0.75rem !important;
            }
            .game-page-section .headtail-body {
                padding: 12px 10px !important;
                min-height: 165px !important;
            }
            .game-page-section .headtail-wrapper,
            .game-page-section .game--card {
                padding: 14px 12px !important;
                border-radius: 12px !important;
            }
            .game-page-section .game-contet-title {
                font-size: 16px !important;
                margin-bottom: 10px !important;
            }
            .game-page-section .headtail-slect {
                gap: 12px !important;
                margin: 12px 0 !important;
            }
            .game-page-section .headtail-slect__box {
                height: 95px !important;
                width: 95px !important;
            }
            .game-page-section .ab-card-wrapper {
                gap: 10px !important;
                max-width: 310px !important;
                margin: 0 auto !important;
            }
            .game-page-section .ab-card-title {
                font-size: 13px !important;
                padding: 3px 8px !important;
            }
            .game-page-section .ab-card {
                gap: 6px !important;
            }
            .game-page-section .coin-flipbox,
            .game-page-section #coin {
                transform: scale(0.82);
            }
        }
    </style>
@endpush

@push('script')
    <script>
        "use strict";
        $(document).on('click touchstart', function(e) {
            $('.win-loss-popup').removeClass('active');
        });

        // Automatically attach 3D Casino Chips (10, 20, 50, 100, 200, 500) to any game with input[name=invest]
        $(function() {
            var $investInputs = $('input[name="invest"]');
            if ($investInputs.length && !$('.chips-container').length) {
                var chipBarHtml = `
                    <div class="universal-chip-bar">
                        <div class="d-flex align-items-center gap-1 flex-wrap">
                            <button type="button" class="u-chip-btn u-chip-10" data-chip="10">10</button>
                            <button type="button" class="u-chip-btn u-chip-20" data-chip="20">20</button>
                            <button type="button" class="u-chip-btn u-chip-50" data-chip="50">50</button>
                            <button type="button" class="u-chip-btn u-chip-100" data-chip="100">100</button>
                            <button type="button" class="u-chip-btn u-chip-200" data-chip="200">200</button>
                            <button type="button" class="u-chip-btn u-chip-500" data-chip="500">500</button>
                        </div>
                        <button type="button" class="u-chip-clear">Clear</button>
                    </div>
                `;
                $investInputs.each(function() {
                    var $inp = $(this);
                    var $group = $inp.closest('.input-group');
                    if ($group.length && !$group.next('.universal-chip-bar').length) {
                        $group.after(chipBarHtml);
                    }
                });

                $(document).on('click', '.u-chip-btn', function(e) {
                    e.preventDefault();
                    var val = parseFloat($(this).data('chip')) || 0;
                    var $inp = $('input[name="invest"]').first();
                    var cur = parseFloat($inp.val()) || 0;
                    $inp.val(cur + val).trigger('input').trigger('change');
                    try {
                        if (typeof playAudio === 'function' && typeof audioAssetPath !== 'undefined') {
                            playAudio(audioAssetPath, "click.mp3");
                        }
                    } catch (err) {}
                });

                $(document).on('click', '.u-chip-clear', function(e) {
                    e.preventDefault();
                    $('input[name="invest"]').val('').trigger('input').trigger('change');
                });
            }
        });
    </script>
@endpush
