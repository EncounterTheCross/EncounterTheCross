<?php

namespace App\Taig\Components;

use LogicException;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class Modal
{
    use DefaultActionTrait;

    public string $variant = 'default';

    public function getVariantClasses(): string
    {
        return match ($this->variant) {
            'default' => 'text-white bg-blue-500 hover:bg-blue-700',
            'success' => 'text-white bg-green-600 hover:bg-green-700',
            'danger' => 'text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:ring-red-300 focus:outline-none',
            default => throw new LogicException(sprintf('Unknown button type "%s"', $this->variant)),
        };
    }
}
