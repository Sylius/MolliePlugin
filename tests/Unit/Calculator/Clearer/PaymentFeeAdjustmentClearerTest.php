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

namespace Tests\Sylius\MolliePlugin\Unit\Calculator\Clearer;

use PHPUnit\Framework\TestCase;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\MolliePlugin\Calculator\Clearer\PaymentFeeAdjustmentClearer;
use Sylius\MolliePlugin\Model\AdjustmentInterface;
use Sylius\MolliePlugin\Provider\PaymentSurchargeAdjustmentsProvider;

final class PaymentFeeAdjustmentClearerTest extends TestCase
{
    public function testItRemovesEveryTypeTheProviderLists(): void
    {
        $types = [
            AdjustmentInterface::FIXED_AMOUNT_ADJUSTMENT,
            AdjustmentInterface::PERCENTAGE_ADJUSTMENT,
            AdjustmentInterface::PERCENTAGE_AND_AMOUNT_ADJUSTMENT,
        ];

        $removed = [];
        $order = $this->createMock(OrderInterface::class);
        $order->method('removeAdjustments')->willReturnCallback(
            function (?string $type = null) use (&$removed): void {
                $removed[] = $type;
            },
        );

        (new PaymentFeeAdjustmentClearer(new PaymentSurchargeAdjustmentsProvider($types)))->clear($order);

        $this->assertSame($types, $removed);
    }

    public function testItRemovesACustomTypeAddedToTheProvider(): void
    {
        $removed = [];
        $order = $this->createMock(OrderInterface::class);
        $order->method('removeAdjustments')->willReturnCallback(
            function (?string $type = null) use (&$removed): void {
                $removed[] = $type;
            },
        );

        $provider = new PaymentSurchargeAdjustmentsProvider(['shop_own_surcharge']);

        (new PaymentFeeAdjustmentClearer($provider))->clear($order);

        $this->assertSame(['shop_own_surcharge'], $removed);
    }
}
