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

namespace Sylius\MolliePlugin\StateMachine\Applicator;

use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;

interface SubscriptionAndPaymentIdApplicatorInterface
{
    public function execute(MollieSubscriptionInterface $subscription, string $paymentId): void;
}
