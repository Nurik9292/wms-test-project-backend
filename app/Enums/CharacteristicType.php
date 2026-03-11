<?php

declare(strict_types=1);

namespace App\Enums;

enum CharacteristicType: string
{
    case String = 'string';
    case Number = 'number';
    case Boolean = 'boolean';
}
