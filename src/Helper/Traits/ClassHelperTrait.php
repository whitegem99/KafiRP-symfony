<?php declare(strict_types=1);

namespace App\Helper\Traits;

use ReflectionException;

trait ClassHelperTrait
{
    public static function GetClassShortName(): string
    {
        try {
            return (new \ReflectionClass(static::class))->getShortName();
        } catch (ReflectionException $e) {
            throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }
}