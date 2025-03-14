<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Factory;

use Sylius\MolliePlugin\Payments\MethodsInterface;

interface MethodsFactoryInterface
{
    public function createNew(): MethodsInterface;
}
