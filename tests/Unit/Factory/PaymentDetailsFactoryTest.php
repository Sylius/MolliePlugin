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
use Sylius\MolliePlugin\Entity\MollieSubscriptionConfigurationInterface;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\MolliePlugin\Factory\PaymentDetailsFactory;
use Sylius\MolliePlugin\Factory\PaymentDetailsFactoryInterface;

final class PaymentDetailsFactoryTest extends TestCase
{
    private PaymentDetailsFactoryInterface $paymentDetailsFactory;

    protected function setUp(): void
    {
        $this->paymentDetailsFactory = new PaymentDetailsFactory();
    }

    public function testImplementsPaymentDetailsFactoryInterface(): void
    {
        $this->assertInstanceOf(PaymentDetailsFactoryInterface::class, $this->paymentDetailsFactory);
    }

    public function testCreatesPaymentDetailsForSubscriptionAndOrder(): void
    {
        $subscriptionConfigurationMock = $this->createMock(MollieSubscriptionConfigurationInterface::class);
        $orderMock = $this->createMock(OrderInterface::class);

        $details = [
            'gateway' => [
                'metadata' => [
                    'gateway' => 'test_gateway',
                ],
            ],
            'metadata' => [
                'gateway' => 'test_gateway',
            ],
        ];

        $subscriptionConfigurationMock->expects($this->once())->method('getPaymentDetailsConfiguration')
            ->willReturn($details);
        $subscriptionConfigurationMock->expects($this->once())->method('getMandateId')->willReturn(null);

        $this->assertSame([
            'recurring' => true,
            'cartToken' => null,
            'mandateId' => null,
            'metadata' => [
                'gateway' => $details['metadata']['gateway'],
            ],
        ], $this->paymentDetailsFactory->createForSubscriptionAndOrder(
            $subscriptionConfigurationMock,
            $orderMock,
        ));
    }
}
