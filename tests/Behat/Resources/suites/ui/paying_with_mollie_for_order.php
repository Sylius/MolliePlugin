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

use Behat\Config\Config;
use Behat\Config\Filter\TagFilter;
use Behat\Config\Profile;
use Behat\Config\Suite;
use Behat\MinkExtension\Context\MinkContext;

return (new Config())
    ->withProfile(
        (new Profile('default'))
        ->withSuite(
            (new Suite('ui_paying_with_mollie_for_order'))
            ->withPaths('features/admin', 'features/shop')
            ->withContexts(
                'sylius.behat.context.hook.doctrine_orm',
            )
            ->withContexts(
                'sylius.behat.context.transform.address',
                'sylius.behat.context.transform.customer',
                'sylius.behat.context.transform.lexical',
                'sylius.behat.context.transform.locale',
                'sylius.behat.context.transform.order',
                'sylius.behat.context.transform.payment',
                'sylius.behat.context.transform.product',
                'sylius.behat.context.transform.shared_storage',
                'sylius.behat.context.transform.shipping_method',
                'sylius.behat.context.transform.tax_category',
                'sylius.behat.context.transform.tax_rate',
                'sylius.behat.context.transform.zone',
            )
            ->withContexts(
                'sylius.behat.context.setup.cart',
                'sylius.behat.context.setup.channel',
                'sylius.behat.context.setup.currency',
                'sylius.behat.context.setup.geographical',
                'sylius.behat.context.setup.locale',
                'sylius.behat.context.setup.order',
                'sylius.behat.context.setup.payment',
                'sylius.behat.context.setup.product',
                'sylius.behat.context.setup.shipping',
                'sylius.behat.context.setup.shop_security',
                'sylius.behat.context.setup.taxation',
                'sylius.behat.context.setup.user',
                'sylius.behat.context.setup.zone',
                'sylius.behat.context.setup.admin_security',
            )
            ->withContexts(
                'sylius_mollie.behat.context.setup.mollie',
            )
            ->withContexts(
                'sylius.behat.context.ui.shop.cart',
                'sylius.behat.context.ui.shop.checkout',
                'sylius.behat.context.ui.shop.checkout.addressing',
                'sylius.behat.context.ui.shop.checkout.complete',
                'sylius.behat.context.ui.shop.checkout.order_details',
                'sylius.behat.context.ui.shop.checkout.payment',
                'sylius.behat.context.ui.shop.checkout.shipping',
                'sylius.behat.context.ui.shop.checkout.thank_you',
            )
            ->withContexts(
                'sylius_mollie.behat.context.ui.shop.checkout',
                'sylius_mollie.behat.context.setup.product',
                'sylius.behat.context.ui.admin.managing_orders',
                'sylius_mollie.behat.context.ui.admin.order',
                'sylius_mollie.behat.context.ui.shop.product',
                MinkContext::class,
            )
            ->withFilter(new TagFilter('@paying_with_mollie_for_order&&@ui')),
        ),
    )
;
