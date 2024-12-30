<?php

/**
 * @Author: jwamser
 *
 * @CreateAt: 2/25/23
 * Project: EncounterTheCross
 * File Name: QuestionsAndConcernsTrait.php
 */

namespace App\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait QuestionsAndConcernsTrait
{
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $questionsOrComments = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $healthConcerns = null;

    public function getQuestionsOrComments(): ?string
    {
        return $this->questionsOrComments;
    }

    public function setQuestionsOrComments(?string $questionsOrComments): self
    {
        $this->questionsOrComments = $questionsOrComments;

        return $this;
    }

    public function getHealthConcerns(): ?string
    {
        return $this->healthConcerns;
    }

    public function setHealthConcerns(?string $healthConcerns): void
    {
        $this->healthConcerns = $healthConcerns;
    }
}
