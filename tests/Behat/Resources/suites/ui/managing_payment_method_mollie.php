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
            (new Suite('ui_managing_payment_method_mollie'))
            ->withPaths('features/admin', 'features/shop')
            ->withContexts(
                'sylius.behat.context.hook.doctrine_orm',
            )
            ->withContexts(
                'sylius.behat.context.transform.address',
                'sylius.behat.context.transform.customer',
                'sylius.behat.context.transform.locale',
                'sylius.behat.context.transform.payment',
                'sylius.behat.context.transform.product',
                'sylius.behat.context.transform.shared_storage',
                'sylius.behat.context.transform.shipping_method',
            )
            ->withContexts(
                'sylius_mollie.behat.context.setup.mollie',
            )
            ->withContexts(
                'sylius.behat.context.setup.channel',
                'sylius.behat.context.setup.currency',
                'sylius.behat.context.setup.locale',
                'sylius.behat.context.setup.order',
                'sylius.behat.context.setup.payment',
                'sylius.behat.context.setup.product',
                'sylius.behat.context.setup.admin_security',
                'sylius.behat.context.setup.shipping',
                'sylius.behat.context.setup.user',
                'sylius.behat.context.setup.zone',
            )
            ->withContexts(
                'sylius.behat.context.ui.admin.managing_payment_methods',
                'sylius.behat.context.ui.admin.notification',
                'sylius.behat.context.ui.save',
                'sylius.behat.context.ui.shop.locale',
            )
            ->withContexts(
                'sylius_mollie.behat.context.ui.admin.managing_payment_method_mollie',
            )
            ->withFilter(new TagFilter('@managing_mollie_payment_method&&@ui')),
        ),
    )
;
