<?php

declare(strict_types=1);

namespace App\Enums;

enum ProductType: string
{
    case Regular = 'regular';
    case Donor = 'donor';
    case Component = 'component';
}
