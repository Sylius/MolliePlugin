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

namespace Sylius\MolliePlugin\Creator;

use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\MolliePlugin\Entity\OrderInterface;

interface PaymentDataCreatorInterface
{
    /**
     * @return array<string, mixed>
     */
    public function create(
        OrderInterface $order,
        PaymentInterface $payment,
        GatewayConfigInterface $gatewayConfig,
        string $methodId,
        bool $isSubscription,
        string $backUrl,
        ?string $customerMollieId,
    ): array;
}
