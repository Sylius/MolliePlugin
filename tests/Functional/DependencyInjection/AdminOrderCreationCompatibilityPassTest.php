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

namespace Tests\Sylius\MolliePlugin\Functional\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\AbstractCompilerPassTestCase;
use Sylius\MolliePlugin\DependencyInjection\AdminOrderCreationCompatibilityPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final class AdminOrderCreationCompatibilityPassTest extends AbstractCompilerPassTestCase
{
    private const INTERNAL_PAYMENT_TOKEN_PROVIDER_ID = 'sylius_mollie.payum.provider.payment_token';

    private const PLUGIN_PAYMENT_TOKEN_PROVIDER_ID = 'Sylius\AdminOrderCreationPlugin\Provider\PaymentTokenProvider';

    private const INTERNAL_CUSTOMER_PROVIDER_ID = 'sylius_mollie.provider.customer';

    private const PLUGIN_CUSTOMER_PROVIDER_ID = 'Sylius\AdminOrderCreationPlugin\Provider\CustomerProvider';

    public function testItDoesNotOverwriteAdminOrderCreationPluginPaymentTokenProvider(): void
    {
        $this->setDefinition(self::INTERNAL_PAYMENT_TOKEN_PROVIDER_ID, $this->createMock(Definition::class));

        $this->compile();

        $this->assertContainerBuilderHasService(self::INTERNAL_PAYMENT_TOKEN_PROVIDER_ID);
        $this->assertContainerBuilderNotHasService(self::PLUGIN_PAYMENT_TOKEN_PROVIDER_ID);
    }

    public function testItOverwritesAdminOrderCreationPluginPaymentTokenProvider(): void
    {
        $this->setDefinition(self::INTERNAL_PAYMENT_TOKEN_PROVIDER_ID, $this->createMock(Definition::class));
        $this->setDefinition(self::PLUGIN_PAYMENT_TOKEN_PROVIDER_ID, $this->createMock(Definition::class));

        $this->compile();

        $this->assertContainerBuilderHasService(self::INTERNAL_PAYMENT_TOKEN_PROVIDER_ID);
        $this->assertContainerBuilderHasService(self::PLUGIN_PAYMENT_TOKEN_PROVIDER_ID);

        $this::assertSame(
            $this->container->getDefinition(self::PLUGIN_PAYMENT_TOKEN_PROVIDER_ID),
            $this->container->getDefinition(self::INTERNAL_PAYMENT_TOKEN_PROVIDER_ID),
        );
    }

    public function testItDoesNotOverwriteAdminOrderCreationPluginCustomerProvider(): void
    {
        $this->setDefinition(self::INTERNAL_CUSTOMER_PROVIDER_ID, $this->createMock(Definition::class));

        $this->compile();

        $this->assertContainerBuilderHasService(self::INTERNAL_CUSTOMER_PROVIDER_ID);
        $this->assertContainerBuilderNotHasService(self::PLUGIN_CUSTOMER_PROVIDER_ID);
    }

    public function testItOverwritesAdminOrderCreationPluginCustomerProvider(): void
    {
        $this->setDefinition(self::INTERNAL_CUSTOMER_PROVIDER_ID, $this->createMock(Definition::class));
        $this->setDefinition(self::PLUGIN_CUSTOMER_PROVIDER_ID, $this->createMock(Definition::class));

        $this->compile();

        $this->assertContainerBuilderHasService(self::INTERNAL_CUSTOMER_PROVIDER_ID);
        $this->assertContainerBuilderHasService(self::PLUGIN_CUSTOMER_PROVIDER_ID);

        $this::assertSame(
            $this->container->getDefinition(self::PLUGIN_CUSTOMER_PROVIDER_ID),
            $this->container->getDefinition(self::INTERNAL_CUSTOMER_PROVIDER_ID),
        );
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new AdminOrderCreationCompatibilityPass());
    }
}
