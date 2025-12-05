<?php

namespace App\Service\SpamDetection\Model;

class SpamScore implements \JsonSerializable
{
    private int $totalScore;
    private array $details;

    public function __construct(int $totalScore, array $details)
    {
        $this->totalScore = $totalScore;
        $this->details = $details;
    }

    public function getTotalScore(): int
    {
        return $this->totalScore;
    }

    public function getDetails(): array
    {
        return $this->details;
    }

    public function toArray(): array
    {
        return [
            'totalScore' => $this->totalScore,
            'details' => $this->details,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}