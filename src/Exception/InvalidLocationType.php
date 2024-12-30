<?php

/**
 * @Author: jwamser
 *
 * @CreateAt: 2/25/23
 * Project: EncounterTheCross
 * File Name: InvalidLocationType.php
 */

namespace App\Exception;

use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;

class InvalidLocationType extends InvalidArgumentException
{
    #[Pure]
    public function __construct(string $fieldName, string $locationType)
    {
        $message = sprintf(
            'Invalid Location type for Event `%s`. Only `%s` Locations can be added.',
            $fieldName,
            $locationType
        );
        parent::__construct($message);
    }
}
