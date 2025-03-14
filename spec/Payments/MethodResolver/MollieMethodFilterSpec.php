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

namespace spec\SyliusMolliePlugin\Payments\MethodResolver;

use SyliusMolliePlugin\Entity\GatewayConfigInterface;
use SyliusMolliePlugin\Factory\MollieGatewayFactory;
use SyliusMolliePlugin\Factory\MollieSubscriptionGatewayFactory;
use SyliusMolliePlugin\Payments\MethodResolver\MollieMethodFilter;
use SyliusMolliePlugin\Payments\MethodResolver\MollieMethodFilterInterface;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Core\Model\PaymentMethodInterface;

final class MollieMethodFilterSpec extends ObjectBehavior
{
    function it_is_initializable(): void
    {
        $this->shouldHaveType(MollieMethodFilter::class);
    }

    function it_implements_interface(): void
    {
        $this->shouldImplement(MollieMethodFilterInterface::class);
    }

    function it_returns_non_recurring_method(
        PaymentMethodInterface $method1,
        PaymentMethodInterface $method2,
        GatewayConfigInterface $config1,
        GatewayConfigInterface $config2
    ): void {
        $paymentMethods = [
            $method1,
            $method2
        ];
        $method1->getGatewayConfig()->willReturn($config1);
        $config1->getFactoryName()->willReturn(MollieSubscriptionGatewayFactory::FACTORY_NAME);

        $method2->getGatewayConfig()->willReturn($config2);
        $config2->getFactoryName()->willReturn('not_mollie_subscription');

        $this->nonRecurringFilter($paymentMethods)->shouldReturn([$method2]);
    }

    function it_returns_recurring_method(
        PaymentMethodInterface $method1,
        PaymentMethodInterface $method2,
        GatewayConfigInterface $config1,
        GatewayConfigInterface $config2
    ): void {
        $paymentMethods = [
            $method1,
            $method2
        ];
        $method1->getGatewayConfig()->willReturn($config1);
        $config1->getFactoryName()->willReturn(MollieGatewayFactory::FACTORY_NAME);

        $method2->getGatewayConfig()->willReturn($config2);
        $config2->getFactoryName()->willReturn('mollie_subscription');

        $this->recurringFilter($paymentMethods)->shouldReturn([$method2]);
    }
}
