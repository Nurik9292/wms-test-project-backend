<?php

declare(strict_types=1);

namespace App\Enums;

enum ProductUnit: string
{
    case Pieces = 'pcs';
    case Kilograms = 'kg';
    case Liters = 'l';
}
