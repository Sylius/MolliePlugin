<?php

/*
 * This file is part of the Sylius Mollie Plugin package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Tests\Sylius\MolliePlugin\Unit\Factory;

use PHPUnit\Framework\TestCase;
use Sylius\MolliePlugin\Factory\MethodsFactory;
use Sylius\MolliePlugin\Factory\MethodsFactoryInterface;
use Sylius\MolliePlugin\Registry\PaymentMethodRegistryInterface;

final class MethodsFactoryTest extends TestCase
{
    private MethodsFactoryInterface $methodsFactory;

    protected function setUp(): void
    {
        $this->methodsFactory = new MethodsFactory();
    }

    public function testImplementsMethodsFactoryInterface(): void
    {
        $this->assertInstanceOf(MethodsFactoryInterface::class, $this->methodsFactory);
    }

    public function testCreatesNewMethod(): void
    {
        $method = $this->methodsFactory->createNew();
        $this->assertInstanceOf(PaymentMethodRegistryInterface::class, $method);
    }
}
