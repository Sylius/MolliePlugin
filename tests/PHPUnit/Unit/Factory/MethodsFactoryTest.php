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

namespace Tests\SyliusMolliePlugin\PHPUnit\Unit\Factory;

use PHPUnit\Framework\TestCase;
use SyliusMolliePlugin\Factory\MethodsFactory;
use SyliusMolliePlugin\Factory\MethodsFactoryInterface;
use SyliusMolliePlugin\Payments\MethodsInterface;

final class MethodsFactoryTest extends TestCase
{
    private MethodsFactoryInterface $methodsFactory;

    protected function setUp(): void
    {
        $this->methodsFactory = new MethodsFactory();
    }

    function testImplementsMethodsFactoryInterface(): void
    {
        $this->assertInstanceOf(MethodsFactoryInterface::class, $this->methodsFactory);
    }

    function testCreatesNewMethod(): void
    {
        $method = $this->methodsFactory->createNew();
        $this->assertInstanceOf(MethodsInterface::class, $method);
    }
}