<?php

namespace App;

enum Service: int
{
    CASE FIREFIGHTERS = 1;
    CASE POLICE = 2;
    CASE EMERGENCY = 3;
    CASE EDDS = 4;
    CASE ANTITERROR = 5;

    public function label():string
    {
        return match ($this) {
            self::FIREFIGHTERS => 'Пожарные',
            self::POLICE => 'Полиция',
            self::EMERGENCY => 'Скорая',
            self::EDDS => 'Газ',
            self::ANTITERROR => 'Антитеррор'
        };
    }
}
