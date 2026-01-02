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

namespace Sylius\MolliePlugin\Repository\Query;

use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;

interface MollieBasedPaymentMethodQueryInterface
{
    /** @return iterable<PaymentMethodInterface> */
    public function getAllExcludingCode(string $code): iterable;

    public function getOneByChannelAndFactoryName(
        ChannelInterface $channel,
        string $factoryName,
    ): ?PaymentMethodInterface;
}
