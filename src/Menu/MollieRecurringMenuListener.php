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

final class MollieRecurringMenuListener
{
    public function buildMenu(MenuBuilderEvent $menuBuilderEvent): void
    {
        $menu = $menuBuilderEvent->getMenu();
        $menuItem =
            $menu
                ->getChild('mollie');

        if (null === $menuItem) {
            return;
        }

        $menuItem
            ->addChild('mollie_subscriptions', [
                'route' => 'sylius_mollie_admin_mollie_subscription_index',
            ])
            ->setLabel('sylius_mollie.ui.mollie_subscriptions')
            ->setLabelAttribute('icon', 'cart')
        ;
    }
}
