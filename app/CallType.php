<?php

namespace App;

enum CallType: int
{
    CASE FALSE = 1;
    CASE CHILD = 2;
    CASE HELP = 3;
    CASE FIREFIGHTERS = 4;
    CASE POLICE = 5;
    CASE EMERGENCY = 6;
    CASE GAS = 7;
    CASE EDDS = 8;

    public function label()
    {
        return match($this) {
            self::FALSE => 'Ложный',
            self::CHILD => 'Детская шалость',
            self::HELP => 'Справочный',
            self::FIREFIGHTERS => 'Пожарные',
            self::POLICE => 'Полиция',
            self::EMERGENCY => 'Скорая',
            self::GAS => 'Газ',
            self::EDDS => 'ЕДДС',
        };
    }
    public function relatedService()
    {
        return match($this) {
            self::FALSE => null,
            self::CHILD => null,
            self::HELP => null,
            self::FIREFIGHTERS => Service::FIREFIGHTERS,
            self::POLICE => Service::POLICE,
            self::EMERGENCY => Service::EMERGENCY,
            self::GAS => Service::EDDS,
            self::EDDS => Service::EDDS,
        };
    }

    public function hasRelatedService()
    {
        return $this->relatedService() !== null;
    }
}
