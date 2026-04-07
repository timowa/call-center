<?php

namespace App;

use App\Models\EddsReport;
use App\Models\FireReport;

enum Service: int
{
    CASE FIREFIGHTERS = 1;
    CASE POLICE = 2;
    CASE EMERGENCY = 3;
    CASE EDDS = 4;
    CASE ANTITERROR = 5;
    CASE GAS = 6;

    public function label():string
    {
        return match ($this) {
            self::FIREFIGHTERS => 'Пожарные',
            self::POLICE => 'Полиция',
            self::EMERGENCY => 'Скорая',
            self::EDDS => 'ЕДДС/ЖКХ',
            self::ANTITERROR => 'Антитеррор',
            self::GAS => 'Газ'
        };
    }

    public function getRelatedModel()
    {
        return match ($this) {
            self::FIREFIGHTERS => FireReport::class,
            self::POLICE => null,
            self::EMERGENCY => null,
            self::EDDS => EddsReport::class,
            self::ANTITERROR => null,
            self::GAS => null,
        };
    }

    public function hasRelatedModel()
    {
        return $this->getRelatedModel() !== null;
    }
}
