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

namespace Sylius\MolliePlugin\Documentation;

use Symfony\Contracts\Translation\TranslatorInterface;

final class DocumentationLinks implements DocumentationLinksInterface
{
    public const DOCUMENTATION_LINKS = [
        'single_click' => 'https://help.mollie.com/hc/en-us/articles/115000671249-What-are-single-click-payments-and-how-does-it-work-',
        'mollie_components' => 'https://www.mollie.com/en/news/post/better-checkout-flows-with-mollie-components',
        'payment_methods' => 'https://docs.mollie.com/orders/why-use-orders',
        'api_key' => 'https://www.mollie.com/dashboard/developers/api-keys',
    ];

    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function getSingleClickDoc(): string
    {
        $link = \sprintf(
            '<a target="_blank" href="%s"> %s </a>',
            self::DOCUMENTATION_LINKS['single_click'],
            $this->translator->trans('sylius_mollie_plugin.ui.mollie_single_click')
        );

        return $this->translator->trans('sylius_mollie_plugin.ui.read_more_single_click_enabled', [
            '%link%' => $link,
        ]);
    }

    public function getMollieComponentsDoc(): string
    {
        $link = \sprintf(
            '<a target="_blank" href="%s"> %s </a>',
            self::DOCUMENTATION_LINKS['mollie_components'],
            $this->translator->trans('sylius_mollie_plugin.ui.mollie_components')
        );

        return $this->translator->trans('sylius_mollie_plugin.ui.read_more_enable_components', [
            '%link%' => $link,
        ]);
    }

    public function getPaymentMethodDoc(): string
    {
        return \sprintf(
            '%s <a target="_blank" href="%s"> %s </a> %s',
            $this->translator->trans('sylius_mollie_plugin.ui.click'),
            self::DOCUMENTATION_LINKS['payment_methods'],
            $this->translator->trans('sylius_mollie_plugin.ui.here'),
            $this->translator->trans('sylius_mollie_plugin.ui.payment_methods_doc')
        );
    }

    public function getApiKeyDoc(): string
    {
        return \sprintf(
            '%s <a target="_blank" href="%s"> %s </a> %s',
            $this->translator->trans('sylius_mollie_plugin.ui.find_you_api_key'),
            self::DOCUMENTATION_LINKS['api_key'],
            $this->translator->trans('sylius_mollie_plugin.ui.mollie_profile'),
            $this->translator->trans('sylius_mollie_plugin.ui.it_starts_with')
        );
    }
}
