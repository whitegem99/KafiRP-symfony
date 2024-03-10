<?php
namespace App\Enum;

use App\Helper\Traits\ClassHelperTrait;
use App\Model\EnumObject;
use RuntimeException;

abstract class BaseEnum
{
    use ClassHelperTrait;

    /**
     * @param mixed $value
     * @return bool
     */
    public static function IsValid($value): bool
    {
        return in_array($value, self::GetValues(), true);
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public static function GetValidValue($value)
    {
        if (false === self::IsValid($value)) {
            throw new RuntimeException(self::GetClassShortName() . ' : Invalid enum value: ' . var_export($value, true));
        }

        return $value;
    }

    public static function GetValidKey($value): string
    {
        if ($key = self::GetName($value)) {
            return $key;
        }

        throw new RuntimeException(self::GetClassShortName() . ' : Invalid enum value: ' . var_export($value, true));
    }

    /**
     * @param string $key
     * @return int|string
     */
    public static function GetValidValueByKey(string $key)
    {
        foreach (self::GetValues() as $index => $value) {
            if (strtolower($index) === strtolower($key)) {
                return $value;
            }
        }

        throw new RuntimeException(self::GetClassShortName() . ' : Invalid enum key: ' . var_export($key, true));
    }

    public static function GetName($value)
    {
        $reflector = new \ReflectionClass(get_called_class());
        foreach($reflector->getConstants() as $key => $val)
            if ($val == $value)
                return $key;

        return null;
    }

    public static function GetValues()
    {
        $reflector = new \ReflectionClass(get_called_class());
        return $reflector->getConstants();
    }

    /**
     * Gets values as an EnumObject array
     * @return EnumObject[]
     */
    public static function GetValuesAsObjectArray()
    {
        $objects = [];
        $className = get_called_class();
        $reflector = new \ReflectionClass($className);

        foreach($reflector->getConstants() as $key => $val) {
            $obj = new EnumObject();
            $obj->Id = $val;
            $obj->Code = $key;

            $objects[] = $obj;
        }

        return $objects;
    }
}