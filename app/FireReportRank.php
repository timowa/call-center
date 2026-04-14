<?php

namespace App;

enum FireReportRank: int
{
    CASE RANK_1 = 1;
    CASE RANK_1_BIS = 2;
    CASE RANK_2 = 3;
    CASE RANK_3 = 4;

    public function label()
    {
        return match ($this) {
            self::RANK_1 => '1',
            self::RANK_1_BIS => '1 бис',
            self::RANK_2 => '2',
            self::RANK_3 => '3',
        };
    }
}
