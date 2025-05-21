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

namespace Sylius\MolliePlugin\Payum\Action\Refund;

use Sylius\MolliePlugin\Payum\Action\BaseApiAwareAction;

abstract class BaseRefundAction extends BaseApiAwareAction
{
    public function shouldBeRefunded(\ArrayObject $details): bool
    {
        $refundMetadata = $details['metadata']['refund'] ?? null;
        if (null === $refundMetadata) {
            return false;
        }

        if (isset($refundMetadata['items']) && [] !== $refundMetadata['items']) {
            $items = $refundMetadata['items'];

            return !empty($items[0]);
        }
        if (isset($refundMetadata['shipments']) && [] !== $refundMetadata['shipments']) {
            $shipments = $refundMetadata['shipments'];

            return !empty($shipments[0]);
        }

        return false;
    }
}
