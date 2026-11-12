<?php

namespace App\Games;

use App\Constants\Status;

class Roulette extends Game
{
    protected $alias = 'roulette';
    protected $resultShowOnStart = true;
    protected $extraValidationRule = [
        'invest' => 'required|numeric|gt:0'
    ];

    public static $groups = [
        '1_12'          => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
        '13_24'         => [13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24],
        '25_36'         => [25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36],
        '1_18'          => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18],
        '19_36'         => [19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36],
        'even'          => [2, 4, 6, 8, 10, 12, 14, 16, 18, 20, 22, 24, 26, 28, 30, 32, 34, 36],
        'odd'           => [1, 3, 5, 7, 9, 11, 13, 15, 17, 19, 21, 23, 25, 27, 29, 31, 33, 35],
        'red'           => [1, 3, 5, 7, 9, 12, 14, 16, 18, 19, 21, 23, 25, 27, 30, 32, 34, 36],
        'black'         => [2, 4, 6, 8, 10, 11, 13, 15, 17, 20, 22, 24, 26, 28, 29, 31, 33, 35],
        '2_1_1'         => [3, 6, 9, 12, 15, 18, 21, 24, 27, 30, 33, 36],
        '2_1_2'         => [2, 5, 8, 11, 14, 17, 20, 23, 26, 29, 32, 35],
        '2_1_3'         => [1, 4, 7, 10, 13, 16, 19, 22, 25, 28, 31, 34]
    ];

    protected function gameResult()
    {
        // European roulette: 0 to 36
        $random = rand(0, 36);

        $betsInput = $this->request->bets;
        $totalWinAmount = 0;

        if (!empty($betsInput)) {
            $bets = is_array($betsInput) ? $betsInput : json_decode($betsInput, true);
        } else if (!empty($this->request->choose)) {
            $choose = $this->request->choose;
            if (strpos((string)$choose, ',') !== false) {
                $raw = explode(',', (string)$choose);
                $perChip = $this->request->invest / max(count($raw), 1);
                $bets = [];
                foreach ($raw as $r) {
                    $bets[trim($r)] = $perChip;
                }
            } else {
                $bets = [$choose => $this->request->invest];
            }
        } else {
            $bets = ['0' => $this->request->invest];
        }

        if (!is_array($bets) || empty($bets)) {
            $bets = ['0' => $this->request->invest];
        }

        foreach ($bets as $spot => $chipAmount) {
            $chipAmount = floatval($chipAmount);
            if ($chipAmount <= 0) continue;

            $spotStr = (string)$spot;

            if (isset(self::$groups[$spotStr])) {
                $groupNumbers = self::$groups[$spotStr];
                if (in_array($random, $groupNumbers)) {
                    if (in_array($spotStr, ['red', 'black', 'even', 'odd', '1_18', '19_36'])) {
                        $totalWinAmount += $chipAmount * 2;
                    } else if (in_array($spotStr, ['1_12', '13_24', '25_36', '2_1_1', '2_1_2', '2_1_3'])) {
                        $totalWinAmount += $chipAmount * 3;
                    }
                }
            } else if (is_numeric($spotStr)) {
                $targetNum = intval($spotStr);
                if ($targetNum === $random) {
                    $totalWinAmount += $chipAmount * 36;
                }
            }
        }

        $win = ($totalWinAmount > 0) ? Status::WIN : Status::LOSS;

        $winLossData['win_status'] = $win;
        $winLossData['result']     = $random;
        $winLossData['win_amount'] = round($totalWinAmount, 2);
        return $winLossData;
    }
}
