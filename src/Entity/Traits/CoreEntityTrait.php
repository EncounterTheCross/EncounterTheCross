<?php

/**
 * @Author: jwamser
 *
 * @CreateAt: 3/1/23
 * Project: EncounterTheCross
 * File Name: CoreEntityTrait.php
 */

namespace App\Entity\Traits;

use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @see https://symfony.com/bundles/StofDoctrineExtensionsBundle/current/configuration.html#use-the-doctrineextensions-library Extensions Config Documentation
 *
 * Class used to create the base fields of the entities. See config documentations if adding more to make sure all settings are configured.
 */
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false, hardDelete: false)]
trait CoreEntityTrait
{
    use EntityIdTrait;
    /**
     * Hook SoftDeleteable behavior
     * updates deletedAt field.
     */
    use SoftDeleteableEntity;
    /**
     * Hook timestampable behavior
     * updates createdAt, updatedAt fields.
     */
    use TimestampableEntity;
}
