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

namespace Sylius\MolliePlugin\TemplateEmailTerms;

use Sylius\MolliePlugin\Entity\TemplateMollieEmailInterface;

final class Options
{
    public const PAYMENT_LINK = 'sylius_mollie_plugin.ui.paymentlink';

    public const PAYMENT_LINK_ABANDONED = 'sylius_mollie_plugin.ui.paymentlinkAbandoned';

    public static function getAvailableEmailTemplate(): array
    {
        return [
            self::PAYMENT_LINK => TemplateMollieEmailInterface::PAYMENT_LINK,
            self::PAYMENT_LINK_ABANDONED => TemplateMollieEmailInterface::PAYMENT_LINK_ABANDONED,
        ];
    }
}
