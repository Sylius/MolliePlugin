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

namespace SyliusMolliePlugin\Helper;

use Sylius\RefundPlugin\Model\OrderItemUnitRefund;
use Sylius\RefundPlugin\Model\ShipmentRefund;

final class ConvertRefundData implements ConvertRefundDataInterface
{
    /** @var IntToStringConverterInterface */
    private $intToStringConverter;

    public function __construct(IntToStringConverterInterface $intToStringConverter)
    {
        $this->intToStringConverter = $intToStringConverter;
    }

    public function convert(array $data, string $currency): array
    {
        $value = 0;

        foreach ($data as $items) {
            foreach ($this->getTotal($items) as $total) {
                $value += $total;
            }
        }

        return [
            'currency' => $currency,
            'value' => $this->intToStringConverter->convertIntToString($value),
        ];
    }

    private function getTotal(array $refundsData): iterable
    {
        /** @var OrderItemUnitRefund|ShipmentRefund $refundData */
        foreach ($refundsData as $refundData) {
            yield $refundData->total();
        }
    }
}
