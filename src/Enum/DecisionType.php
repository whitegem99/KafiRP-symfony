<?php declare(strict_types=1);

namespace App\Enum;

class DecisionType extends BaseEnum
{
    public const REJECTED = 1;
    public const APPROVED = 2;
    public const NOT_DECIDED = 3;
    public const MAYBE_APPROVED = 4;
}