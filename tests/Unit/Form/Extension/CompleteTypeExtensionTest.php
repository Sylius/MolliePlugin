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

namespace Tests\Sylius\MolliePlugin\Unit\Form\Extension;

use Payum\Core\Model\GatewayConfigInterface;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\MolliePlugin\Form\Extension\CompleteTypeExtension;
use Sylius\MolliePlugin\Form\Type\DirectDebitType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Valid;

final class CompleteTypeExtensionTest extends TestCase
{
    private CompleteTypeExtension $completeTypeExtension;

    protected function setUp(): void
    {
        $this->completeTypeExtension = new CompleteTypeExtension();
    }

    public function testInitializable(): void
    {
        $this->assertInstanceOf(CompleteTypeExtension::class, $this->completeTypeExtension);
        $this->assertInstanceOf(AbstractTypeExtension::class, $this->completeTypeExtension);
    }

    public function testBuildsForm(): void
    {
        $builderMock = $this->createMock(FormBuilderInterface::class);
        $orderMock = $this->createMock(OrderInterface::class);
        $paymentMock = $this->createMock(PaymentInterface::class);
        $methodMock = $this->createMock(PaymentMethodInterface::class);
        $configMock = $this->createMock(GatewayConfigInterface::class);

        $builderMock->expects($this->once())
            ->method('getData')
            ->willReturn($orderMock)
        ;

        $orderMock->expects($this->once())
            ->method('getLastPayment')
            ->willReturn($paymentMock)
        ;

        $paymentMock->expects($this->once())
            ->method('getMethod')
            ->willReturn($methodMock)
        ;

        $paymentMock->expects($this->once())
            ->method('getDetails')
            ->willReturn([
                'molliePaymentMethods' => 'directdebit',
            ])
        ;

        $methodMock->expects($this->exactly(2))
        ->method('getGatewayConfig')
            ->willReturn($configMock)
        ;

        $configMock->expects($this->once())
            ->method('getFactoryName')
            ->willReturn('mollie_subscription')
        ;

        $builderMock->expects($this->once())
            ->method('add')
            ->with(
                'directDebit',
                DirectDebitType::class,
                [
                    'mapped' => false,
                    'validation_groups' => ['sylius'],
                    'constraints' => [
                        new Valid(),
                    ],
                ],
            )
        ;

        $this->completeTypeExtension->buildForm($builderMock, []);
    }
}
