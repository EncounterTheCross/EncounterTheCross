<?php

namespace App\Twig\Components;

use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

trait GridConfigurationTrait
{
    #[ExposeInTemplate('size')]
    #[ArrayShape(['string' => 'int'])]
    private array $cardColumns;

    public function getCardColumns(): array
    {
        // TODO this currently doesn't have an implementation in the twig templates
        return $this->cardColumns;
    }

    private function setCardColumns(
        array|int $config,
    ): void {
        if (is_int($config)) {
            $config = [
                'breakpoints' => [['lg' => $config]],
            ];
        }
        $resolver = new OptionsResolver();
        $resolver->setDefault('breakpoints', function (OptionsResolver $breakpointResolver): void {
            $breakpointResolver
                ->setPrototype(true)
                ->setRequired(['lg'])
                ->setDefaults([
                    'xs' => null,
                    'sm' => null,
                    'md' => null,
                    'lg' => null,
                    'xl' => null,
                    'xxl' => null,
                ]);
        });

        $this->cardColumns = $resolver->resolve($config);
    }
}
