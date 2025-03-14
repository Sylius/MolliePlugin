<?php


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
                ->setLabel('sylius_mollie_plugin.ui.mollie_gateway_label');

        $menuItem
            ->addChild('mollie_logger', [
                'route' => 'sylius_mollie_plugin_admin_mollie_logger_index',
            ])
            ->setLabel('sylius_mollie_plugin.ui.mollie_loggers')
            ->setLabelAttribute('icon', 'tags');

        $menuItem
            ->addChild('mollie_product_type', [
                'route' => 'sylius_mollie_plugin_admin_product_type_index',
            ])
            ->setLabel('sylius_mollie_plugin.ui.mollie_product_type')
            ->setLabelAttribute('icon', 'sitemap');
    }
}
