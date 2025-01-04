<?php

namespace App\Taig\Components;

use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(template: 'components/Taig/Modal.html.twig')]
class Modal
{
    use DefaultActionTrait;

    #[LiveProp]
    public bool $closeButton = false;

    // Add this to allow any child content without prop validation
    public function allowsExtraAttributes(): bool
    {
        return true;
    }
}
