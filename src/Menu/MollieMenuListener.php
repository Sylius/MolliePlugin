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

final class MollieMenuListener
{
    public function buildMenu(MenuBuilderEvent $menuBuilderEvent): void
    {
        $menu = $menuBuilderEvent->getMenu();
        $menuItem =
            $menu
                ->addChild('mollie')
                ->setLabel('sylius_mollie.ui.mollie_gateway_label');

        $menuItem
            ->addChild('mollie_logger', [
                'route' => 'sylius_mollie_admin_mollie_logger_index',
            ])
            ->setLabel('sylius_mollie.ui.mollie_loggers')
            ->setLabelAttribute('icon', 'tags');

        $menuItem
            ->addChild('mollie_product_type', [
                'route' => 'sylius_mollie_admin_product_type_index',
            ])
            ->setLabel('sylius_mollie.ui.mollie_product_type')
            ->setLabelAttribute('icon', 'sitemap');
    }
}
