<?php

namespace App;

enum FireReportPlan:int
{
    CASE PLAN_1 = 1;
    CASE PLAN_2 = 2;
    CASE PLAN_3 = 3;
    CASE PLAN_4 = 4;

    public function label()
    {
        return match ($this) {
            self::PLAN_1 => 'Первый',
            self::PLAN_2 => 'Второй',
            self::PLAN_3 => 'Третий',
            self::PLAN_4 => 'Четвертый',
        };
    }
}
