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

return (new Config())
    ->withProfile(
        (new Profile('default'))
        ->withSuite(
            (new Suite('ui_refunding_mollie_payment'))
            ->withPaths('features/admin', 'features/shop')
            ->withContexts(
                'sylius.behat.context.hook.doctrine_orm',
                'sylius.behat.context.hook.mailer',
            )
            ->withContexts(
                'sylius.behat.context.setup.channel',
                'sylius.behat.context.setup.currency',
                'sylius.behat.context.setup.customer',
                'sylius.behat.context.setup.geographical',
                'sylius.behat.context.setup.order',
                'sylius.behat.context.setup.payment',
                'sylius.behat.context.setup.product',
                'sylius.behat.context.setup.product_taxon',
                'sylius.behat.context.setup.promotion',
                'sylius.behat.context.setup.admin_security',
                'sylius.behat.context.setup.shop_security',
                'sylius.behat.context.setup.shipping',
                'sylius.behat.context.setup.taxation',
                'sylius.behat.context.setup.taxonomy',
                'sylius.behat.context.setup.zone',
                'sylius.behat.context.setup.admin_user',
                'sylius.behat.context.setup.user',
            )
            ->withContexts(
                'sylius_mollie.behat.context.setup.mollie',
                'sylius_mollie.behat.context.setup.order',
            )
            ->withContexts(
                'sylius.behat.context.transform.address',
                'sylius.behat.context.transform.channel',
                'sylius.behat.context.transform.country',
                'sylius.behat.context.transform.currency',
                'sylius.behat.context.transform.customer',
                'sylius.behat.context.transform.lexical',
                'sylius.behat.context.transform.order',
                'sylius.behat.context.transform.payment',
                'sylius.behat.context.transform.product',
                'sylius.behat.context.transform.product_variant',
                'sylius.behat.context.transform.promotion',
                'sylius.behat.context.transform.shipping_method',
                'sylius.behat.context.transform.tax_category',
                'sylius.behat.context.transform.taxon',
                'sylius.behat.context.transform.zone',
                'sylius.behat.context.transform.shared_storage',
            )
            ->withContexts(
                'sylius.behat.context.ui.admin.managing_orders',
                'sylius.behat.context.ui.admin.notification',
                'sylius.behat.context.ui.channel',
                'sylius.behat.context.ui.email',
                'sylius.behat.context.ui.shop.cart',
                'sylius.behat.context.ui.shop.checkout',
                'sylius.behat.context.ui.shop.checkout.addressing',
                'sylius.behat.context.ui.shop.checkout.complete',
                'sylius.behat.context.ui.shop.currency',
            )
            ->withContexts(
                'sylius_mollie.behat.context.ui.admin.refund',
            )
            ->withFilter(new TagFilter('@refunding_mollie_payment&&@ui')),
        ),
    )
;
