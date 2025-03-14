<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Menu;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class MollieEmailTemplateMenuListener
{
    public function buildMenu(MenuBuilderEvent $menuBuilderEvent): void
    {
        $menu = $menuBuilderEvent->getMenu();
        $cmsRootMenuItem =
            $menu
                ->getChild('mollie');

        if (null === $cmsRootMenuItem) {
            return;
        }

        $cmsRootMenuItem
            ->addChild('mollie_email_template', [
                'route' => 'sylius_mollie_plugin_admin_template_mollie_email_index',
            ])
            ->setLabel('sylius_mollie_plugin.ui.template_mollie_emails')
            ->setLabelAttribute('icon', 'envelope');
    }
}
