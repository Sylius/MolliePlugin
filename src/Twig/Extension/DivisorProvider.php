<?php

namespace Sylius\MolliePlugin\Twig\Extension;

use Sylius\MolliePlugin\Provider\Divisor\DivisorProviderInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class DivisorProvider extends AbstractExtension
{
    /** @var DivisorProviderInterface */
    private $divisorProvider;

    public function __construct(DivisorProviderInterface $divisorProvider)
    {
        $this->divisorProvider = $divisorProvider;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'mollie_get_divisor',
                [$this->divisorProvider, 'getDivisor'],
                ['is_safe' => ['html']]
            )
        ];
    }
}
