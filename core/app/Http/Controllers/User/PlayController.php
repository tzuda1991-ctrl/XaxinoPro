<?php

namespace App\Http\Controllers\User;

use App\Games\GamePlayer;
use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameLog;
use Illuminate\Http\Request;

class PlayController extends Controller {
    public function playGame($alias, $isDemo = null) {
        $game      = Game::active()->where('alias', $alias)->firstOrFail();
        $pageTitle = "Play " . $game->name;
        $user      = auth()->user();

        if ($isDemo) {
            abort_if($isDemo !== 'demo', 404);
        }

        if (@$user) {
            // Auto-complete any stuck incomplete game logs older than 20 seconds
            GameLog::where('user_id', $user->id)
                ->where('status', 0)
                ->update(['status' => 1, 'win_status' => 0]);
        }

        if ($isDemo === 'demo' && @$user && $user->demo_balance <= 0) {
            $user->demo_balance = 50000;
            $user->save();
        }

        $balance = ($isDemo === 'demo') ? @$user->demo_balance : @$user->balance;

        return view('Template::user.games.' . $alias, compact('game', 'pageTitle', 'isDemo', 'balance'));
    }

    public function investGame(Request $request, $alias, $isDemo = null) {
        try {
            $gamePlayer = new GamePlayer($alias, $isDemo);
            return $gamePlayer->startGame();
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 200);
        }
    }

    public function gameEnd(Request $request, $alias, $isDemo = null) {
        try {
            $gamePlayer = new GamePlayer($alias, $isDemo);
            return $gamePlayer->completeGame();
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 200);
        }
    }
}
