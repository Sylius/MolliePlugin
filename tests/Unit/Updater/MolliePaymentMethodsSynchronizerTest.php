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

namespace Tests\Sylius\MolliePlugin\Unit\Updater;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Channel\Context\ChannelNotFoundException;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Repository\PaymentMethodRepositoryInterface;
use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\MolliePlugin\Exceptions\MollieMethodsException;
use Sylius\MolliePlugin\Payum\Factory\MollieGatewayFactory;
use Sylius\MolliePlugin\Payum\Factory\MollieSubscriptionGatewayFactory;
use Sylius\MolliePlugin\Updater\MollieMethodsUpdaterInterface;
use Sylius\MolliePlugin\Updater\MolliePaymentMethodsSynchronizer;

final class MolliePaymentMethodsSynchronizerTest extends TestCase
{
    private MockObject|PaymentMethodRepositoryInterface $paymentMethodRepository;

    private ChannelContextInterface|MockObject $channelContext;

    private MockObject|MollieMethodsUpdaterInterface $mollieMethodsUpdater;

    private MolliePaymentMethodsSynchronizer $synchronizer;

    protected function setUp(): void
    {
        $this->paymentMethodRepository = $this->createMock(PaymentMethodRepositoryInterface::class);
        $this->channelContext = $this->createMock(ChannelContextInterface::class);
        $this->mollieMethodsUpdater = $this->createMock(MollieMethodsUpdaterInterface::class);

        $this->synchronizer = new MolliePaymentMethodsSynchronizer(
            $this->paymentMethodRepository,
            $this->channelContext,
            $this->mollieMethodsUpdater,
        );
    }

    public function testSyncReturnsEarlyWhenChannelNotFound(): void
    {
        $this->channelContext
            ->expects(self::once())
            ->method('getChannel')
            ->willThrowException(new ChannelNotFoundException());

        $this->paymentMethodRepository
            ->expects(self::never())
            ->method('findBy');

        $this->mollieMethodsUpdater
            ->expects(self::never())
            ->method('update');

        $this->synchronizer->sync();
    }

    public function testSyncProcessesOnlyEnabledPaymentMethods(): void
    {
        $channel = $this->createMock(ChannelInterface::class);

        $this->channelContext
            ->expects(self::once())
            ->method('getChannel')
            ->willReturn($channel);

        $this->paymentMethodRepository
            ->expects(self::once())
            ->method('findBy')
            ->with(['enabled' => true])
            ->willReturn([]);

        $this->mollieMethodsUpdater
            ->expects(self::never())
            ->method('update');

        $this->synchronizer->sync();
    }

    public function testSyncSkipsPaymentMethodsNotInCurrentChannel(): void
    {
        $channel = $this->createMock(ChannelInterface::class);

        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $channels = new ArrayCollection();

        $paymentMethod
            ->expects(self::once())
            ->method('getChannels')
            ->willReturn($channels);

        $this->channelContext
            ->expects(self::once())
            ->method('getChannel')
            ->willReturn($channel);

        $this->paymentMethodRepository
            ->expects(self::once())
            ->method('findBy')
            ->with(['enabled' => true])
            ->willReturn([$paymentMethod]);

        $this->mollieMethodsUpdater
            ->expects(self::never())
            ->method('update');

        $this->synchronizer->sync();
    }

    /**
     * @dataProvider invalidGatewayConfigProvider
     */
    public function testSyncSkipsPaymentMethodsWithInvalidGatewayConfig(
        bool $hasGatewayConfig,
        ?string $factoryName = null,
    ): void {
        $channel = $this->createMock(ChannelInterface::class);

        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $channels = new ArrayCollection([$channel]);

        $gatewayConfig = null;
        if ($hasGatewayConfig) {
            $gatewayConfig = $this->createMock(GatewayConfigInterface::class);
            $gatewayConfig
                ->expects(self::once())
                ->method('getFactoryName')
                ->willReturn($factoryName);
        }

        $paymentMethod
            ->expects(self::once())
            ->method('getChannels')
            ->willReturn($channels);

        $paymentMethod
            ->expects(self::once())
            ->method('getGatewayConfig')
            ->willReturn($gatewayConfig);

        $this->channelContext
            ->expects(self::once())
            ->method('getChannel')
            ->willReturn($channel);

        $this->paymentMethodRepository
            ->expects(self::once())
            ->method('findBy')
            ->with(['enabled' => true])
            ->willReturn([$paymentMethod]);

        $this->mollieMethodsUpdater
            ->expects(self::never())
            ->method('update');

        $this->synchronizer->sync();
    }

    public static function invalidGatewayConfigProvider(): \Generator
    {
        yield 'null gateway config' => [
            'hasGatewayConfig' => false,
            'factoryName' => null,
        ];

        yield 'unsupported factory name stripe' => [
            'hasGatewayConfig' => true,
            'factoryName' => 'stripe',
        ];

        yield 'empty factory name' => [
            'hasGatewayConfig' => true,
            'factoryName' => '',
        ];
    }

    /**
     * @dataProvider validMollieGatewayFactoriesProvider
     */
    public function testSyncUpdatesValidMollieGatewayPaymentMethods(string $factoryName): void
    {
        $channel = $this->createMock(ChannelInterface::class);

        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $channels = new ArrayCollection([$channel]);
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);

        $gatewayConfig
            ->expects(self::once())
            ->method('getFactoryName')
            ->willReturn($factoryName);

        $paymentMethod
            ->expects(self::once())
            ->method('getChannels')
            ->willReturn($channels);

        $paymentMethod
            ->expects(self::once())
            ->method('getGatewayConfig')
            ->willReturn($gatewayConfig);

        $this->channelContext
            ->expects(self::once())
            ->method('getChannel')
            ->willReturn($channel);

        $this->paymentMethodRepository
            ->expects(self::once())
            ->method('findBy')
            ->with(['enabled' => true])
            ->willReturn([$paymentMethod]);

        $this->mollieMethodsUpdater
            ->expects(self::once())
            ->method('update')
            ->with($gatewayConfig);

        $this->synchronizer->sync();
    }

    public static function validMollieGatewayFactoriesProvider(): \Generator
    {
        yield 'mollie gateway' => [
            'factoryName' => MollieGatewayFactory::FACTORY_NAME,
        ];

        yield 'mollie subscription gateway' => [
            'factoryName' => MollieSubscriptionGatewayFactory::FACTORY_NAME,
        ];
    }

    public function testSyncHandlesMollieMethodsExceptionGracefully(): void
    {
        $channel = $this->createMock(ChannelInterface::class);

        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $channels = new ArrayCollection([$channel]);
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);

        $gatewayConfig
            ->expects(self::once())
            ->method('getFactoryName')
            ->willReturn(MollieGatewayFactory::FACTORY_NAME);

        $paymentMethod
            ->expects(self::once())
            ->method('getChannels')
            ->willReturn($channels);

        $paymentMethod
            ->expects(self::once())
            ->method('getGatewayConfig')
            ->willReturn($gatewayConfig);

        $this->channelContext
            ->expects(self::once())
            ->method('getChannel')
            ->willReturn($channel);

        $this->paymentMethodRepository
            ->expects(self::once())
            ->method('findBy')
            ->with(['enabled' => true])
            ->willReturn([$paymentMethod]);

        $this->mollieMethodsUpdater
            ->expects(self::once())
            ->method('update')
            ->with($gatewayConfig)
            ->willThrowException(new MollieMethodsException('API error'));

        $this->synchronizer->sync();
    }

    public function testSyncProcessesMultiplePaymentMethods(): void
    {
        $channel = $this->createMock(ChannelInterface::class);

        $paymentMethod1 = $this->createMock(PaymentMethodInterface::class);
        $paymentMethod2 = $this->createMock(PaymentMethodInterface::class);
        $paymentMethod3 = $this->createMock(PaymentMethodInterface::class);

        $channels = new ArrayCollection([$channel]);

        $gatewayConfig1 = $this->createMock(GatewayConfigInterface::class);
        $gatewayConfig2 = $this->createMock(GatewayConfigInterface::class);
        $gatewayConfig3 = $this->createMock(GatewayConfigInterface::class);

        $gatewayConfig1
            ->expects(self::once())
            ->method('getFactoryName')
            ->willReturn(MollieGatewayFactory::FACTORY_NAME);

        $gatewayConfig2
            ->expects(self::once())
            ->method('getFactoryName')
            ->willReturn(MollieSubscriptionGatewayFactory::FACTORY_NAME);

        $gatewayConfig3
            ->expects(self::once())
            ->method('getFactoryName')
            ->willReturn('stripe');

        $paymentMethod1
            ->expects(self::once())
            ->method('getChannels')
            ->willReturn($channels);

        $paymentMethod1
            ->expects(self::once())
            ->method('getGatewayConfig')
            ->willReturn($gatewayConfig1);

        $paymentMethod2
            ->expects(self::once())
            ->method('getChannels')
            ->willReturn($channels);

        $paymentMethod2
            ->expects(self::once())
            ->method('getGatewayConfig')
            ->willReturn($gatewayConfig2);

        $paymentMethod3
            ->expects(self::once())
            ->method('getChannels')
            ->willReturn($channels);

        $paymentMethod3
            ->expects(self::once())
            ->method('getGatewayConfig')
            ->willReturn($gatewayConfig3);

        $this->channelContext
            ->expects(self::once())
            ->method('getChannel')
            ->willReturn($channel);

        $this->paymentMethodRepository
            ->expects(self::once())
            ->method('findBy')
            ->with(['enabled' => true])
            ->willReturn([$paymentMethod1, $paymentMethod2, $paymentMethod3]);

        $this->mollieMethodsUpdater
            ->expects(self::exactly(2))
            ->method('update')
            ->willReturnCallback(function ($config) use ($gatewayConfig1, $gatewayConfig2) {
                static $callCount = 0;
                ++$callCount;

                if ($callCount === 1) {
                    $this->assertSame($gatewayConfig1, $config);
                } elseif ($callCount === 2) {
                    $this->assertSame($gatewayConfig2, $config);
                }
            });

        $this->synchronizer->sync();
    }

    public function testSyncProcessesMixOfValidAndInvalidPaymentMethods(): void
    {
        $channel = $this->createMock(ChannelInterface::class);

        $validPaymentMethod = $this->createMock(PaymentMethodInterface::class);
        $invalidPaymentMethod1 = $this->createMock(PaymentMethodInterface::class);
        $invalidPaymentMethod2 = $this->createMock(PaymentMethodInterface::class);
        $exceptionPaymentMethod = $this->createMock(PaymentMethodInterface::class);

        $channels = new ArrayCollection([$channel]);
        $emptyChannels = new ArrayCollection();

        $validGatewayConfig = $this->createMock(GatewayConfigInterface::class);
        $exceptionGatewayConfig = $this->createMock(GatewayConfigInterface::class);

        $validGatewayConfig
            ->expects(self::once())
            ->method('getFactoryName')
            ->willReturn(MollieGatewayFactory::FACTORY_NAME);

        $exceptionGatewayConfig
            ->expects(self::once())
            ->method('getFactoryName')
            ->willReturn(MollieSubscriptionGatewayFactory::FACTORY_NAME);

        $validPaymentMethod
            ->expects(self::once())
            ->method('getChannels')
            ->willReturn($channels);

        $validPaymentMethod
            ->expects(self::once())
            ->method('getGatewayConfig')
            ->willReturn($validGatewayConfig);

        $invalidPaymentMethod1
            ->expects(self::once())
            ->method('getChannels')
            ->willReturn($emptyChannels);

        $invalidPaymentMethod2
            ->expects(self::once())
            ->method('getChannels')
            ->willReturn($channels);

        $invalidPaymentMethod2
            ->expects(self::once())
            ->method('getGatewayConfig')
            ->willReturn(null);

        $exceptionPaymentMethod
            ->expects(self::once())
            ->method('getChannels')
            ->willReturn($channels);

        $exceptionPaymentMethod
            ->expects(self::once())
            ->method('getGatewayConfig')
            ->willReturn($exceptionGatewayConfig);

        $this->channelContext
            ->expects(self::once())
            ->method('getChannel')
            ->willReturn($channel);

        $this->paymentMethodRepository
            ->expects(self::once())
            ->method('findBy')
            ->with(['enabled' => true])
            ->willReturn([
                $invalidPaymentMethod1,
                $validPaymentMethod,
                $invalidPaymentMethod2,
                $exceptionPaymentMethod,
            ]);

        $this->mollieMethodsUpdater
            ->expects(self::exactly(2))
            ->method('update')
            ->willReturnCallback(function ($config) use ($validGatewayConfig, $exceptionGatewayConfig) {
                static $callCount = 0;
                ++$callCount;

                if ($callCount === 1) {
                    $this->assertSame($validGatewayConfig, $config);

                    return null;
                }
                if ($callCount === 2) {
                    $this->assertSame($exceptionGatewayConfig, $config);

                    throw new MollieMethodsException('API error');
                }
            });

        $this->synchronizer->sync();
    }

    public function testSyncContinuesProcessingAfterException(): void
    {
        $channel = $this->createMock(ChannelInterface::class);

        $paymentMethod1 = $this->createMock(PaymentMethodInterface::class);
        $paymentMethod2 = $this->createMock(PaymentMethodInterface::class);

        $channels = new ArrayCollection([$channel]);

        $gatewayConfig1 = $this->createMock(GatewayConfigInterface::class);
        $gatewayConfig2 = $this->createMock(GatewayConfigInterface::class);

        $gatewayConfig1
            ->expects(self::once())
            ->method('getFactoryName')
            ->willReturn(MollieGatewayFactory::FACTORY_NAME);

        $gatewayConfig2
            ->expects(self::once())
            ->method('getFactoryName')
            ->willReturn(MollieGatewayFactory::FACTORY_NAME);

        $paymentMethod1
            ->expects(self::once())
            ->method('getChannels')
            ->willReturn($channels);

        $paymentMethod1
            ->expects(self::once())
            ->method('getGatewayConfig')
            ->willReturn($gatewayConfig1);

        $paymentMethod2
            ->expects(self::once())
            ->method('getChannels')
            ->willReturn($channels);

        $paymentMethod2
            ->expects(self::once())
            ->method('getGatewayConfig')
            ->willReturn($gatewayConfig2);

        $this->channelContext
            ->expects(self::once())
            ->method('getChannel')
            ->willReturn($channel);

        $this->paymentMethodRepository
            ->expects(self::once())
            ->method('findBy')
            ->with(['enabled' => true])
            ->willReturn([$paymentMethod1, $paymentMethod2]);

        $this->mollieMethodsUpdater
            ->expects(self::exactly(2))
            ->method('update')
            ->willReturnCallback(function ($config) use ($gatewayConfig1, $gatewayConfig2) {
                static $callCount = 0;
                ++$callCount;

                if ($callCount === 1) {
                    $this->assertSame($gatewayConfig1, $config);

                    throw new MollieMethodsException('First error');
                }
                if ($callCount === 2) {
                    $this->assertSame($gatewayConfig2, $config);

                    return null;
                }
            });

        $this->synchronizer->sync();
    }
}
