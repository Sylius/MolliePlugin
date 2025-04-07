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

namespace Sylius\MolliePlugin\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class AdminOrderCreationCompatibilityPass implements CompilerPassInterface
{
    private const PAYMENT_TOKEN_PROVIDER_ID = 'Sylius\AdminOrderCreationPlugin\Provider\PaymentTokenProvider';

    private const CUSTOMER_PROVIDER_ID = 'Sylius\AdminOrderCreationPlugin\Provider\CustomerProvider';

    public function process(ContainerBuilder $container): void
    {
        if ($container->has(self::PAYMENT_TOKEN_PROVIDER_ID)) {
            $adminOrderCreationPaymentTokenProviderDefinition = $container
                ->findDefinition(self::PAYMENT_TOKEN_PROVIDER_ID)
            ;

            $container->setDefinition(
                'sylius_mollie.payum.provider.payment_token',
                $adminOrderCreationPaymentTokenProviderDefinition,
            );
        }

        if ($container->has(self::CUSTOMER_PROVIDER_ID)) {
            $adminOrderCreationCustomerProviderDefinition = $container
                ->findDefinition(self::CUSTOMER_PROVIDER_ID)
            ;

            $container->setDefinition(
                'sylius_mollie.provider.customer',
                $adminOrderCreationCustomerProviderDefinition,
            );
        }
    }
}
