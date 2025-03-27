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
    /**
     * Checks if payment should be refunded. As long as there are order items to be refunded, payment will be refunded.
     */
    public function shouldBeRefunded(\ArrayObject $details): bool
    {
        if (isset($details['metadata']['refund']) && array_key_exists('items', $details['metadata']['refund'])) {
            $items = $details['metadata']['refund']['items'];

            return count($items) > 0 && !empty($items[0]);
        }

        return false;
    }
}
