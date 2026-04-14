<?php

namespace App;

enum FireReportTypeOfProtection: int
{
    CASE TYPE_1 = 1;
    CASE TYPE_2 = 2;
    CASE TYPE_3 = 3;
    CASE TYPE_4 = 4;
    CASE TYPE_5 = 5;
    CASE TYPE_6 = 6;
    CASE TYPE_7 = 7;
    CASE TYPE_8 = 8;

    public function label()
    {
        return match ($this) {
            self::TYPE_1 => 'Ведомственная',
            self::TYPE_2 => 'ВПО',
            self::TYPE_3 => 'ДПО',
            self::TYPE_4 => 'МПО',
            self::TYPE_5 => 'Не охраняется',
            self::TYPE_6 => 'ППО',
            self::TYPE_7 => 'СПО',
            self::TYPE_8 => 'ФПС',
        };
    }
}
