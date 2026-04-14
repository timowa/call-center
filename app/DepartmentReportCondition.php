<?php

namespace App;

enum DepartmentReportCondition:int
{
    CASE CREATED = 1;
    CASE ACCEPTED = 2;
    CASE ARRIVED = 3;
    CASE DONE = 4;

    public function label()
    {
        return match ($this) {
            self::CREATED => 'Заявка создана',
            self::ACCEPTED => 'Заявка принята',
            self::ARRIVED => 'Прибытие',
            self::DONE => 'Заявка отработана'
        };
    }
}
