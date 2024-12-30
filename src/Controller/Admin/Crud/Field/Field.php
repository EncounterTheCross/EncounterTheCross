<?php

/**
 * @Author: jwamser
 *
 * @CreateAt: 4/7/23
 * Project: EncounterTheCross
 * File Name: Field.php
 */

namespace App\Controller\Admin\Crud\Field;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;

final class Field implements FieldInterface
{
    use FieldTrait;
    private FieldDto $dto;

    public static function new(string $propertyName, ?string $label = null)
    {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label);
    }

    public static function newFromDto(FieldDto $dto)
    {
        $self = (new self());
        $self->dto = $dto;

        return $self;
    }
}
