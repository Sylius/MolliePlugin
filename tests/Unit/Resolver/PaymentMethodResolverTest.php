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

namespace Tests\Sylius\MolliePlugin\Unit\Resolver;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Result;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Payment\Resolver\PaymentMethodsResolverInterface;
use Sylius\MolliePlugin\Calculator\PaymentFee\ChargedSurchargeMatcherInterface;
use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\MolliePlugin\Entity\OrderInterface as MollieOrderInterface;
use Sylius\MolliePlugin\Exceptions\UnknownPaymentSurchargeType;
use Sylius\MolliePlugin\Filter\MollieMethodFilterInterface;
use Sylius\MolliePlugin\Logger\MollieLoggerActionInterface;
use Sylius\MolliePlugin\Payum\Checker\MollieGatewayFactoryCheckerInterface;
use Sylius\MolliePlugin\Payum\Factory\MollieGatewayFactory;
use Sylius\MolliePlugin\Payum\Factory\MollieSubscriptionGatewayFactory;
use Sylius\MolliePlugin\Repository\Query\MollieBasedPaymentMethodQueryInterface;
use Sylius\MolliePlugin\Resolver\MollieFactoryNameResolverInterface;
use Sylius\MolliePlugin\Resolver\PaymentMethodResolver;

final class PaymentMethodResolverTest extends TestCase
{
    private PaymentMethodsResolverInterface $decoratedResolverMock;

    private MollieBasedPaymentMethodQueryInterface $mollieBasedPaymentMethodQueryMock;

    private MollieFactoryNameResolverInterface $factoryNameResolverMock;

    private MollieMethodFilterInterface $mollieMethodFilterMock;

    private ChargedSurchargeMatcherInterface $chargedSurchargeMatcherMock;

    private MollieGatewayFactoryCheckerInterface $gatewayFactoryCheckerMock;

    private MollieLoggerActionInterface $loggerActionMock;

    private PaymentMethodResolver $resolver;

    protected function setUp(): void
    {
        $this->decoratedResolverMock = $this->createMock(PaymentMethodsResolverInterface::class);
        $this->mollieBasedPaymentMethodQueryMock = $this->createMock(MollieBasedPaymentMethodQueryInterface::class);
        $this->factoryNameResolverMock = $this->createMock(MollieFactoryNameResolverInterface::class);
        $this->mollieMethodFilterMock = $this->createMock(MollieMethodFilterInterface::class);
        $this->chargedSurchargeMatcherMock = $this->createMock(ChargedSurchargeMatcherInterface::class);
        $this->gatewayFactoryCheckerMock = $this->createMock(MollieGatewayFactoryCheckerInterface::class);
        $this->loggerActionMock = $this->createMock(MollieLoggerActionInterface::class);

        $this->resolver = new PaymentMethodResolver(
            $this->decoratedResolverMock,
            $this->mollieBasedPaymentMethodQueryMock,
            $this->factoryNameResolverMock,
            $this->mollieMethodFilterMock,
            $this->entityManagerAssociatingEveryMethodWithTheChannel(),
            $this->chargedSurchargeMatcherMock,
            $this->gatewayFactoryCheckerMock,
            $this->loggerActionMock,
        );
    }

    public function testImplementsPaymentMethodsResolverInterface(): void
    {
        $this->assertInstanceOf(PaymentMethodsResolverInterface::class, $this->resolver);
    }

    public function testItOffersOnlyMollieOnAPlacedOrderCarryingASurcharge(): void
    {
        $mollie = $this->mollieMethod();
        $offline = $this->offlineMethod();

        $payment = $this->paymentOfPlacedOrder([$mollie, $offline]);
        $this->chargedSurchargeMatcherMock->method('chargedSurcharge')->willReturn(500);
        $this->chargedSurchargeMatcherMock->method('gatewayKeepsTheTotal')->willReturn(true);

        $this->assertSame([$mollie], array_values($this->resolver->getSupportedMethods($payment)));
    }

    public function testItOffersNothingWhenNoMollieMethodReproducesTheChargedSurcharge(): void
    {
        $mollie = $this->mollieMethod();
        $offline = $this->offlineMethod();

        $payment = $this->paymentOfPlacedOrder([$mollie, $offline], '000000035');
        $this->chargedSurchargeMatcherMock->method('chargedSurcharge')->willReturn(500);
        $this->chargedSurchargeMatcherMock->method('gatewayKeepsTheTotal')->willReturn(false);

        $this->loggerActionMock->expects($this->once())
            ->method('addNegativeLog')
            ->with($this->matchesRegularExpression('/No payment method reproduces the 500 surcharge charged on order 000000035/'))
        ;

        $this->assertSame([], $this->resolver->getSupportedMethods($payment));
    }

    public function testItHidesMollieOnASurchargeFreeOrderWhenEveryMollieMethodWouldChargeAFee(): void
    {
        $mollie = $this->mollieMethod();
        $offline = $this->offlineMethod();

        $payment = $this->paymentOfPlacedOrder([$mollie, $offline]);
        $this->chargedSurchargeMatcherMock->method('chargedSurcharge')->willReturn(0);
        $this->chargedSurchargeMatcherMock->method('gatewayKeepsTheTotal')->willReturn(false);

        $this->assertSame([$offline], array_values($this->resolver->getSupportedMethods($payment)));
    }

    public function testItOffersEveryGatewayOnASurchargeFreeOrderWithASurchargeFreeMollieMethod(): void
    {
        $mollie = $this->mollieMethod();
        $offline = $this->offlineMethod();

        $payment = $this->paymentOfPlacedOrder([$mollie, $offline]);
        $this->chargedSurchargeMatcherMock->method('chargedSurcharge')->willReturn(0);
        $this->chargedSurchargeMatcherMock->method('gatewayKeepsTheTotal')->willReturn(true);

        $this->assertSame([$mollie, $offline], array_values($this->resolver->getSupportedMethods($payment)));
    }

    public function testItDropsAMollieGatewayWhoseSurchargesCannotBeCompared(): void
    {
        $mollie = $this->mollieMethod();
        $offline = $this->offlineMethod();

        $payment = $this->paymentOfPlacedOrder([$mollie, $offline]);
        $this->chargedSurchargeMatcherMock->method('chargedSurcharge')->willReturn(0);
        $this->chargedSurchargeMatcherMock->method('gatewayKeepsTheTotal')->willThrowException(
            new UnknownPaymentSurchargeType('no calculator supports payment type: custom'),
        );

        $this->loggerActionMock->expects($this->once())
            ->method('addNegativeLog')
            ->with($this->matchesRegularExpression('/Cannot compare the payment surcharges of gateway mollie/'))
        ;

        $this->assertSame([$offline], array_values($this->resolver->getSupportedMethods($payment)));
    }

    public function testItOffersEveryGatewayDuringCheckoutEvenWhenASurchargeIsCharged(): void
    {
        $mollie = $this->mollieMethod();
        $offline = $this->offlineMethod();

        $payment = $this->paymentOfPlacedOrder([$mollie, $offline], '000000035', checkoutCompletedAt: null);

        $this->chargedSurchargeMatcherMock->expects($this->never())->method('chargedSurcharge');
        $this->chargedSurchargeMatcherMock->expects($this->never())->method('gatewayKeepsTheTotal');

        $this->assertSame([$mollie, $offline], array_values($this->resolver->getSupportedMethods($payment)));
    }

    public function testItLeavesASubscriptionOrderToItsOwnMethod(): void
    {
        $subscriptionMethod = $this->createMock(PaymentMethodInterface::class);

        $payment = $this->paymentOfPlacedOrder(
            [],
            '000000001',
            new \DateTimeImmutable(),
            MollieSubscriptionGatewayFactory::FACTORY_NAME,
        );
        $this->mollieBasedPaymentMethodQueryMock->method('getOneByChannelAndFactoryName')->willReturn($subscriptionMethod);

        $this->chargedSurchargeMatcherMock->expects($this->never())->method('gatewayKeepsTheTotal');

        $this->assertSame([$subscriptionMethod], $this->resolver->getSupportedMethods($payment));
    }

    private function mollieMethod(): PaymentMethodInterface
    {
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);
        $gatewayConfig->method('getFactoryName')->willReturn(MollieGatewayFactory::FACTORY_NAME);
        $gatewayConfig->method('getGatewayName')->willReturn(MollieGatewayFactory::FACTORY_NAME);

        $this->gatewayFactoryCheckerMock->method('isMollieGateway')->willReturnCallback(
            fn (\Payum\Core\Model\GatewayConfigInterface $config): bool => MollieGatewayFactory::FACTORY_NAME === $config->getFactoryName(),
        );

        return $this->method($gatewayConfig, 1);
    }

    private function offlineMethod(): PaymentMethodInterface
    {
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);
        $gatewayConfig->method('getFactoryName')->willReturn('offline');
        $gatewayConfig->method('getGatewayName')->willReturn('offline');

        return $this->method($gatewayConfig, 2);
    }

    private function method(GatewayConfigInterface $gatewayConfig, int $position): PaymentMethodInterface
    {
        $method = $this->createMock(PaymentMethodInterface::class);
        $method->method('getGatewayConfig')->willReturn($gatewayConfig);
        $method->method('getId')->willReturn($position);
        $method->method('getPosition')->willReturn($position);

        return $method;
    }

    /**
     * @param PaymentMethodInterface[] $methods
     */
    private function paymentOfPlacedOrder(
        array $methods,
        string $number = '000000001',
        ?\DateTimeInterface $checkoutCompletedAt = new \DateTimeImmutable(),
        string $factoryName = MollieGatewayFactory::FACTORY_NAME,
    ): PaymentInterface {
        $channel = $this->createMock(ChannelInterface::class);
        $channel->method('getId')->willReturn(1);

        $order = $this->createMock(MollieOrderInterface::class);
        $order->method('getChannel')->willReturn($channel);
        $order->method('getNumber')->willReturn($number);
        $order->method('getCheckoutCompletedAt')->willReturn($checkoutCompletedAt);
        $order->method('hasRecurringContents')->willReturn(false);

        $payment = $this->createMock(PaymentInterface::class);
        $payment->method('getOrder')->willReturn($order);

        $this->factoryNameResolverMock->method('resolve')->willReturn($factoryName);
        $this->decoratedResolverMock->method('getSupportedMethods')->willReturn($methods);
        $this->mollieMethodFilterMock->method('nonRecurringFilter')->willReturnArgument(0);

        return $payment;
    }

    /** `filterMethodsByChannel()` asks the database whether each method is enabled for the channel. */
    private function entityManagerAssociatingEveryMethodWithTheChannel(): EntityManagerInterface
    {
        $result = $this->createMock(Result::class);
        $result->method('fetchOne')->willReturn('1');

        $queryBuilder = $this->createMock(QueryBuilder::class);
        foreach (['select', 'from', 'where', 'andWhere', 'setParameter'] as $fluentMethod) {
            $queryBuilder->method($fluentMethod)->willReturnSelf();
        }
        $queryBuilder->method('executeQuery')->willReturn($result);

        $connection = $this->createMock(Connection::class);
        $connection->method('createQueryBuilder')->willReturn($queryBuilder);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getConnection')->willReturn($connection);

        return $entityManager;
    }
}
