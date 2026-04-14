<?php

namespace App;

enum FireReportBarrelType:int
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
            self::TYPE_1 => 'Водопенный',
            self::TYPE_2 => 'Высокого давления',
            self::TYPE_3 => 'Лафетный',
            self::TYPE_4 => 'Пенный',
            self::TYPE_5 => 'Порошковый',
            self::TYPE_6 => 'РУТП',
            self::TYPE_7 => 'Ствол А',
            self::TYPE_8 => 'Ствол Б',
        };
    }
}
