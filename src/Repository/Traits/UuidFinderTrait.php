<?php

/**
 * @Author: jwamser
 *
 * @CreateAt: 3/3/23
 * Project: EncounterTheCross
 * File Name: UuidFinderTrait.php
 */

namespace App\Repository\Traits;

use UuidFactory;

trait UuidFinderTrait
{
    public function findOneByEncodedRowPointer(string $encodedUuid)
    {
        return $this->findOneBy([
            'rowPointer' => UuidFactory::getRowPointerFromBase32($encodedUuid),
        ]);
    }
}
