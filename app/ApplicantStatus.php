<?php

namespace App;

enum ApplicantStatus: int
{
    CASE MARRIED = 1;
    CASE DIVORCED = 2;
    CASE SINGLE = 3;

    public function label()
    {
        return match ($this) {
            self::MARRIED => 'Женат',
            self::DIVORCED => 'Разведен',
            self::SINGLE => 'Холост'
        };
    }
}
