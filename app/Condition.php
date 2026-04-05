<?php

namespace App;

enum Condition: int
{
    case REQUEST = 1;
    case CONNECTING = 2;
    case RESPONSE = 3;
    case AT_WORK = 4;
    case DONE = 5;
    CASE WATCHING = 6;

    public function label(): string
    {
        return match($this) {
            self::REQUEST => 'Запрос в 112',
            self::CONNECTING => 'Подключение',
            self::RESPONSE => 'Реагирование',
            self::AT_WORK => 'В работе',
            self::DONE => 'Отработана',
            self::WATCHING => 'Просмотр',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::REQUEST => 'bg-red-500',
            self::CONNECTING => 'bg-green-500',
            self::RESPONSE => 'bg-indigo-500',
            self::AT_WORK => 'bg-yellow-500',
            self::DONE => 'bg-grey-370',
            self::WATCHING => 'bg-blue-400'
        };
    }

    public static function canSwitchToWatching(int $condition)
    {
        return in_array($condition, [Condition::REQUEST->value, Condition::CONNECTING->value]);
    }

}

