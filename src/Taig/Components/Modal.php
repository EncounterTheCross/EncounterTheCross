<?php

namespace App\Taig\Components;

use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
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


    #[LiveAction]
    public function close(): void
    {
        dump(func_get_args());
        // Add any cleanup here if needed
        // This ensures the modal is properly closed from the server side
    }
}
