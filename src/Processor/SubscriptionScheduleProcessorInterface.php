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

namespace SyliusMolliePlugin\Processor;

use SyliusMolliePlugin\Entity\MollieSubscriptionInterface;

interface SubscriptionScheduleProcessorInterface
{
    public function process(MollieSubscriptionInterface $subscription): void;

    public function processScheduleGeneration(MollieSubscriptionInterface $subscription): void;
}
