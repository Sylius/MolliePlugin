<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Factory;

use Sylius\MolliePlugin\Payments\Methods;
use Sylius\MolliePlugin\Payments\MethodsInterface;

final class MethodsFactory implements MethodsFactoryInterface
{
    public function createNew(): MethodsInterface
    {
        return new Methods();
    }
}
