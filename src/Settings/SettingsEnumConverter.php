<?php

namespace App\Settings;

use App\Enum\SystemModeEnum;
use Symfony\Component\PropertyInfo\Type;
use Tzunghaor\SettingsBundle\Service\SettingConverterInterface;
use UnitEnum;

class SettingsEnumConverter implements SettingConverterInterface
{
    public function supports(Type $type): bool
    {
        //        return true;
        return is_a($type->getClassName(), SystemModeEnum::class, true);
    }

    /**
     * @param UnitEnum $value
     */
    public function convertToString(Type $type, $value): string
    {
        return '';

        return $value->value;
    }

    public function convertFromString(Type $type, string $value)
    {
        return SystemModeEnum::tryFrom($value);
    }
}
