<?php

namespace App\Games;

use App\Constants\Status;

class AndarBahar extends Game
{
    protected $alias = 'andar_bahar';
    protected $extraValidationRule = [
        'choose' => 'required|in:andar,bahar'
    ];

    protected function deck()
    {
        $suits = ['H', 'D', 'C', 'S'];
        $ranks = range(2, 10);
        $faceCards = ['J', 'Q', 'K', 'A'];
        $deck = [];

        foreach ($suits as $suit) {
            foreach ($ranks as $rank) {
                $deck[] = "{$rank}-{$suit}";
            }
            foreach ($faceCards as $face) {
                $deck[] = "{$face}-{$suit}";
            }
        }
        return $deck;
    }

    protected function winLoss()
    {
        $probableWin = $this->demoPlay
            ? $this->game->probable_win_demo
            : $this->game->probable_win;

        $random = mt_rand(0, 100);

        if ($random <= $probableWin) {
            $winLossData['win_status'] = Status::WIN;
            $winLossData['result'] = $this->request->choose;
        } else {
            $winLossData['win_status'] = Status::LOSS;
            $winLossData['result'] = $this->request->choose == 'andar' ? 'bahar' : 'andar';
        }
        return $winLossData;
    }

    protected function getCardRank($card)
    {
        if (!$card || !is_string($card)) {
            return '';
        }
        $parts = explode('-', $card);
        return $parts[0] ?? '';
    }

    protected function gameResult()
    {
        $deck = $this->deck();
        shuffle($deck);

        $randomCard = array_pop($deck);
        $jokerRank = $this->getCardRank($randomCard);

        $matchingCards = [];
        $nonMatchingCards = [];

        foreach ($deck as $c) {
            if ($this->getCardRank($c) === $jokerRank) {
                $matchingCards[] = $c;
            } else {
                $nonMatchingCards[] = $c;
            }
        }

        shuffle($matchingCards);
        shuffle($nonMatchingCards);

        $winLossData = $this->winLoss();
        $winningSide = $winLossData['result']; // 'andar' or 'bahar'

        $andarCards = [];
        $baharCards = [];

        // Deal 2 to 4 non-matching pairs first for exciting, fast gameplay
        $pairsCount = mt_rand(2, 4);
        for ($i = 0; $i < $pairsCount; $i++) {
            if (!empty($nonMatchingCards)) {
                $andarCards[] = array_pop($nonMatchingCards);
            }
            if (!empty($nonMatchingCards)) {
                $baharCards[] = array_pop($nonMatchingCards);
            }
        }

        $finalMatchingCard = !empty($matchingCards) ? array_pop($matchingCards) : $randomCard;

        if ($winningSide === 'andar') {
            $andarCards[] = $finalMatchingCard;
        } else {
            if (!empty($nonMatchingCards)) {
                $andarCards[] = array_pop($nonMatchingCards);
            }
            $baharCards[] = $finalMatchingCard;
        }

        $this->extraResponseOnStart = array_merge($this->extraResponseOnStart, [
            'randomCard' => $randomCard,
            'andarCards' => $andarCards,
            'baharCards' => $baharCards
        ]);

        return $winLossData;
    }
}
