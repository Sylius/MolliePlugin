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

namespace Sylius\MolliePlugin\Menu;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

/**
 * @deprecated since Mollie 3.3 and will be removed in 4.0.
 *
 * @see https://github.com/Sylius/MolliePlugin/blob/3.3/UPGRADE-3.3.md for migration details
 */
final class MollieRecurringMenuListener
{
    public function buildMenu(MenuBuilderEvent $menuBuilderEvent): void
    {
        $menu = $menuBuilderEvent->getMenu();
        $salesMenu = $menu->getChild('sales');

        if (null === $salesMenu) {
            return;
        }

        $salesMenu
            ->addChild('mollie_subscriptions', [
                'route' => 'sylius_mollie_admin_mollie_subscription_index',
            ])
            ->setLabel('sylius_mollie.ui.mollie_subscriptions')
        ;
    }
}
