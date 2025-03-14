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

namespace spec\Sylius\MolliePlugin\Factory;

use Sylius\MolliePlugin\Factory\MethodsFactory;
use Sylius\MolliePlugin\Factory\MethodsFactoryInterface;
use PhpSpec\ObjectBehavior;

final class MethodsFactorySpec extends ObjectBehavior
{
    function it_is_initializable(): void
    {
        $this->shouldHaveType(MethodsFactory::class);
    }

    function it_should_implements_methods_factory_interface(): void
    {
        $this->shouldImplement(MethodsFactoryInterface::class);
    }

    function it_creates_new_method(): void
    {
        $method = $this->createNew();
        $this->createNew()->shouldBeLike($method);
    }
}
