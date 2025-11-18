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

namespace Sylius\MolliePlugin\EventListener\Workflow\OrderCheckout;

use Sylius\MolliePlugin\Updater\MolliePaymentMethodsSynchronizerInterface;
use Symfony\Component\Workflow\Event\CompletedEvent;

final class RefreshPaymentMethodsListener
{
    public function __construct(
        private readonly MolliePaymentMethodsSynchronizerInterface $molliePaymentMethodsSynchronizer,
    ) {
    }

    public function __invoke(CompletedEvent $event): void
    {
        $this->molliePaymentMethodsSynchronizer->sync();
    }
}
