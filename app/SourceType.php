<?php

namespace App;

enum SourceType: int
{
    case INSTANT = 1;
    case CALL = 2;

    public function label()
    {
        return match($this) {
            self::INSTANT => 'Без вызова',
            self::CALL => 'Телефон'
        };
    }
}
