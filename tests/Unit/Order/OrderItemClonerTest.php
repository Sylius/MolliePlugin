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

namespace Tests\Sylius\MolliePlugin\Unit\Order;

use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Order\Factory\OrderItemUnitFactoryInterface;
use Sylius\Component\Order\Model\OrderItemUnitInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Model\VersionedInterface;
use Sylius\MolliePlugin\Cloner\OrderItemCloner;
use Sylius\MolliePlugin\Cloner\OrderItemClonerInterface;

final class OrderItemClonerTest extends TestCase
{
    private FactoryInterface $orderItemFactoryMock;

    private OrderItemUnitFactoryInterface $orderItemUnitFactoryMock;

    private OrderItemCloner $orderItemCloner;

    protected function setUp(): void
    {
        $this->orderItemFactoryMock = $this->createMock(FactoryInterface::class);
        $this->orderItemUnitFactoryMock = $this->createMock(OrderItemUnitFactoryInterface::class);
        $this->orderItemCloner = new OrderItemCloner($this->orderItemFactoryMock, $this->orderItemUnitFactoryMock);
    }

    public function testImplementOrderItemClonerInterface(): void
    {
        $this->assertInstanceOf(OrderItemClonerInterface::class, $this->orderItemCloner);
    }

    public function testClonesOrderItem(): void
    {
        $orderItemMock = $this->createMock(OrderItemInterface::class);
        $clonedOrderItemMock = $this->createMock(OrderItemInterface::class);
        $clonedUnitMock = $this->createMock(OrderItemUnitInterface::class);
        $orderMock = $this->createMock(OrderInterface::class);
        $variantMock = $this->createMock(ProductVariantInterface::class);

        $orderItemQuantity = 5;

        $this->orderItemFactoryMock->expects($this->once())
            ->method('createNew')
            ->willReturn($clonedOrderItemMock);

        $orderItemMock->expects($this->once())->method('getProductName')->willReturn('test_product_name');
        $orderItemMock->expects($this->once())->method('getUnitPrice')->willReturn(59);
        $orderItemMock->expects($this->once())->method('getVariant')->willReturn($variantMock);
        $orderItemMock->expects($this->once())->method('getVariantName')->willReturn('test_variant_name');
        $orderItemMock->expects($this->once())->method('getVersion')->willReturn(3);
        $orderItemMock->expects($this->once())->method('getQuantity')->willReturn($orderItemQuantity);

        $clonedOrderItemMock->expects($this->once())->method('setOrder')->with($orderMock);
        $clonedOrderItemMock->expects($this->once())->method('setProductName')->with('test_product_name');
        $clonedOrderItemMock->expects($this->once())->method('setUnitPrice')->with(59);
        $clonedOrderItemMock->expects($this->once())->method('setVariant')->with($variantMock);
        $clonedOrderItemMock->expects($this->once())->method('setVariantName')->with('test_variant_name');
        $clonedOrderItemMock->expects($this->once())->method('setImmutable')->with(true); // Set to true directly
        $clonedOrderItemMock->expects($this->once())->method('recalculateUnitsTotal');
        $clonedOrderItemMock->expects($this->once())->method('recalculateAdjustmentsTotal');

        if ($clonedOrderItemMock instanceof VersionedInterface) {
            $clonedOrderItemMock->expects($this->once())->method('setVersion')->with(3);
        }

        $this->orderItemUnitFactoryMock->expects($this->exactly($orderItemQuantity))
            ->method('createForItem')
            ->with($clonedOrderItemMock)
            ->willReturn($clonedUnitMock);

        $clonedOrderItemMock->expects($this->exactly($orderItemQuantity))
            ->method('addUnit')
            ->with($clonedUnitMock);

        $result = $this->orderItemCloner->clone($orderItemMock, $orderMock);
        $this->assertSame($clonedOrderItemMock, $result);
    }
}
