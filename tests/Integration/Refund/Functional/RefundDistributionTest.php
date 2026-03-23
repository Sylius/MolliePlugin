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

namespace Tests\Sylius\MolliePlugin\Integration\Refund\Functional;

use Doctrine\ORM\EntityManagerInterface;
use Mollie\Api\MollieApiClient;
use Mollie\Api\Resources\Payment as MolliePayment;
use PHPUnit\Framework\MockObject\MockObject;
use Sylius\Component\Core\Model\Adjustment;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItem;
use Sylius\Component\Core\Model\OrderItemUnit;
use Sylius\Component\Core\Model\Payment;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Model\Shipment;
use Sylius\Component\Core\Model\ShippingMethod;
use Sylius\Component\Core\OrderCheckoutStates;
use Sylius\Component\Core\OrderPaymentStates;
use Sylius\MolliePlugin\Refund\Creator\PaymentRefundCommandCreatorInterface;
use Sylius\RefundPlugin\Command\RefundUnits;
use Sylius\RefundPlugin\Model\OrderItemUnitRefund;
use Sylius\RefundPlugin\Model\ShipmentRefund;
use Sylius\RefundPlugin\Validator\RefundAmountValidatorInterface;
use Tests\Sylius\MolliePlugin\Entity\Order;

final class RefundDistributionTest extends FunctionalTestCase
{
    private MockObject&MollieApiClient $mollieApiClient;

    private PaymentRefundCommandCreatorInterface $creator;

    private RefundAmountValidatorInterface $validator;

    private EntityManagerInterface $entityManager;

    private int $channelId;

    private int $customerId;

    private int $variantId;

    private int $paymentMethodId;

    private int $shippingMethodId;

    private int $orderCounter = 0;

    public function setUp(): void
    {
        parent::setUp();

        $fixtures = $this->loadFixturesFromFiles(['refund_distribution.yaml']);

        $this->mollieApiClient = $this->createMock(MollieApiClient::class);

        $this->creator = self::getContainer()->get('sylius_mollie.refund.creator.payment_refund_command');
        $this->validator = self::getContainer()->get('Sylius\RefundPlugin\Validator\RefundAmountValidatorInterface');
        $this->entityManager = self::getContainer()->get('doctrine.orm.entity_manager');

        $this->channelId = $fixtures['channel']->getId();
        $this->customerId = $fixtures['customer']->getId();
        $this->variantId = $fixtures['cap_variant']->getId();
        $this->paymentMethodId = $fixtures['mollie']->getId();
        $this->shippingMethodId = $fixtures['shipping_method']->getId();

        $this->entityManager->clear();
    }

    /**
     * @dataProvider fullRefundCasesProvider
     *
     * @param int[] $expectedItemAmounts
     */
    public function test_full_refund_distribution(
        int $itemUnitPrice,
        int $itemQuantity,
        int $itemTaxPerUnit,
        bool $itemTaxIncludedInPrice,
        int $shippingAmount,
        int $shippingTax,
        bool $shippingTaxIncludedInPrice,
        array $expectedItemAmounts,
        ?int $expectedShipmentAmount,
    ): void {
        $molliePayment = $this->createOrder(
            itemUnitPrice: $itemUnitPrice,
            itemQuantity: $itemQuantity,
            itemTaxPerUnit: $itemTaxPerUnit,
            itemTaxIncludedInPrice: $itemTaxIncludedInPrice,
            shippingAmount: $shippingAmount,
            shippingTax: $shippingTax,
            shippingTaxIncludedInPrice: $shippingTaxIncludedInPrice,
        );

        $command = $this->creator->fromPayment($molliePayment);

        $this->assertRefundUnits($command, $expectedItemAmounts, $expectedShipmentAmount);

        $this->validator->validateUnits($command->units());
    }

    /**
     * @dataProvider partialRefundCasesProvider
     *
     * @param int[] $expectedItemAmounts
     */
    public function test_partial_refund_distribution(
        int $itemTaxPerUnit,
        bool $itemTaxIncludedInPrice,
        int $shippingTax,
        bool $shippingTaxIncludedInPrice,
        int $refundAmount,
        array $expectedItemAmounts,
        ?int $expectedShipmentAmount,
    ): void {
        $molliePayment = $this->createOrder(
            itemUnitPrice: 1000,
            itemQuantity: 2,
            itemTaxPerUnit: $itemTaxPerUnit,
            itemTaxIncludedInPrice: $itemTaxIncludedInPrice,
            shippingAmount: 500,
            shippingTax: $shippingTax,
            shippingTaxIncludedInPrice: $shippingTaxIncludedInPrice,
            refundAmount: $refundAmount,
        );

        $command = $this->creator->fromPayment($molliePayment);

        $this->assertRefundUnits($command, $expectedItemAmounts, $expectedShipmentAmount);

        $this->validator->validateUnits($command->units());
    }

    /**
     * @return iterable<string, array{
     *     itemUnitPrice: int,
     *     itemQuantity: int,
     *     itemTaxPerUnit: int,
     *     itemTaxIncludedInPrice: bool,
     *     shippingAmount: int,
     *     shippingTax: int,
     *     shippingTaxIncludedInPrice: bool,
     *     expectedItemAmounts: int[],
     *     expectedShipmentAmount: int|null,
     * }>
     */
    public static function fullRefundCasesProvider(): iterable
    {
        yield 'items only, no shipping, no taxes' => [
            'itemUnitPrice' => 1000,
            'itemQuantity' => 2,
            'itemTaxPerUnit' => 0,
            'itemTaxIncludedInPrice' => false,
            'shippingAmount' => 0,
            'shippingTax' => 0,
            'shippingTaxIncludedInPrice' => false,
            'expectedItemAmounts' => [1000, 1000],
            'expectedShipmentAmount' => null,
        ];

        yield 'items with taxes, no shipping' => [
            'itemUnitPrice' => 1000,
            'itemQuantity' => 2,
            'itemTaxPerUnit' => 230,
            'itemTaxIncludedInPrice' => false,
            'shippingAmount' => 0,
            'shippingTax' => 0,
            'shippingTaxIncludedInPrice' => false,
            'expectedItemAmounts' => [1230, 1230],
            'expectedShipmentAmount' => null,
        ];

        yield 'items with shipping, no taxes' => [
            'itemUnitPrice' => 1000,
            'itemQuantity' => 2,
            'itemTaxPerUnit' => 0,
            'itemTaxIncludedInPrice' => false,
            'shippingAmount' => 500,
            'shippingTax' => 0,
            'shippingTaxIncludedInPrice' => false,
            'expectedItemAmounts' => [1000, 1000],
            'expectedShipmentAmount' => 500,
        ];

        yield 'items with taxes, shipping without tax' => [
            'itemUnitPrice' => 1000,
            'itemQuantity' => 2,
            'itemTaxPerUnit' => 230,
            'itemTaxIncludedInPrice' => false,
            'shippingAmount' => 500,
            'shippingTax' => 0,
            'shippingTaxIncludedInPrice' => false,
            'expectedItemAmounts' => [1230, 1230],
            'expectedShipmentAmount' => 500,
        ];

        yield 'items without taxes, shipping with tax' => [
            'itemUnitPrice' => 1000,
            'itemQuantity' => 2,
            'itemTaxPerUnit' => 0,
            'itemTaxIncludedInPrice' => false,
            'shippingAmount' => 500,
            'shippingTax' => 115,
            'shippingTaxIncludedInPrice' => false,
            'expectedItemAmounts' => [1000, 1000],
            'expectedShipmentAmount' => 615,
        ];

        yield 'items and shipping both with taxes' => [
            'itemUnitPrice' => 1000,
            'itemQuantity' => 2,
            'itemTaxPerUnit' => 230,
            'itemTaxIncludedInPrice' => false,
            'shippingAmount' => 500,
            'shippingTax' => 115,
            'shippingTaxIncludedInPrice' => false,
            'expectedItemAmounts' => [1230, 1230],
            'expectedShipmentAmount' => 615,
        ];

        yield 'items with tax included in price, no shipping' => [
            'itemUnitPrice' => 1000,
            'itemQuantity' => 2,
            'itemTaxPerUnit' => 187,
            'itemTaxIncludedInPrice' => true,
            'shippingAmount' => 0,
            'shippingTax' => 0,
            'shippingTaxIncludedInPrice' => false,
            'expectedItemAmounts' => [1000, 1000],
            'expectedShipmentAmount' => null,
        ];

        yield 'items with tax included in price, shipping with tax excluded' => [
            'itemUnitPrice' => 1000,
            'itemQuantity' => 2,
            'itemTaxPerUnit' => 187,
            'itemTaxIncludedInPrice' => true,
            'shippingAmount' => 500,
            'shippingTax' => 115,
            'shippingTaxIncludedInPrice' => false,
            'expectedItemAmounts' => [1000, 1000],
            'expectedShipmentAmount' => 615,
        ];

        yield 'items with tax excluded, shipping with tax included in price' => [
            'itemUnitPrice' => 1000,
            'itemQuantity' => 2,
            'itemTaxPerUnit' => 230,
            'itemTaxIncludedInPrice' => false,
            'shippingAmount' => 500,
            'shippingTax' => 96,
            'shippingTaxIncludedInPrice' => true,
            'expectedItemAmounts' => [1230, 1230],
            'expectedShipmentAmount' => 500,
        ];

        yield 'items and shipping both with tax included in price' => [
            'itemUnitPrice' => 1000,
            'itemQuantity' => 2,
            'itemTaxPerUnit' => 187,
            'itemTaxIncludedInPrice' => true,
            'shippingAmount' => 500,
            'shippingTax' => 96,
            'shippingTaxIncludedInPrice' => true,
            'expectedItemAmounts' => [1000, 1000],
            'expectedShipmentAmount' => 500,
        ];
    }

    /**
     * @return iterable<string, array{
     *     itemTaxPerUnit: int,
     *     itemTaxIncludedInPrice: bool,
     *     shippingTax: int,
     *     shippingTaxIncludedInPrice: bool,
     *     refundAmount: int,
     *     expectedItemAmounts: int[],
     *     expectedShipmentAmount: int|null,
     * }>
     */
    public static function partialRefundCasesProvider(): iterable
    {
        yield 'tax excluded: refund below single unit total' => [
            'itemTaxPerUnit' => 230,
            'itemTaxIncludedInPrice' => false,
            'shippingTax' => 115,
            'shippingTaxIncludedInPrice' => false,
            'refundAmount' => 500,
            'expectedItemAmounts' => [500],
            'expectedShipmentAmount' => null,
        ];

        yield 'tax excluded: refund equals single unit total' => [
            'itemTaxPerUnit' => 230,
            'itemTaxIncludedInPrice' => false,
            'shippingTax' => 115,
            'shippingTaxIncludedInPrice' => false,
            'refundAmount' => 1230,
            'expectedItemAmounts' => [1230],
            'expectedShipmentAmount' => null,
        ];

        yield 'tax excluded: refund spans two units, below items base price total' => [
            'itemTaxPerUnit' => 230,
            'itemTaxIncludedInPrice' => false,
            'shippingTax' => 115,
            'shippingTaxIncludedInPrice' => false,
            'refundAmount' => 1500,
            'expectedItemAmounts' => [270, 1230],
            'expectedShipmentAmount' => null,
        ];

        yield 'tax excluded: refund equals items base price total' => [
            'itemTaxPerUnit' => 230,
            'itemTaxIncludedInPrice' => false,
            'shippingTax' => 115,
            'shippingTaxIncludedInPrice' => false,
            'refundAmount' => 2000,
            'expectedItemAmounts' => [770, 1230],
            'expectedShipmentAmount' => null,
        ];

        yield 'tax excluded: refund between items base price and items with tax' => [
            'itemTaxPerUnit' => 230,
            'itemTaxIncludedInPrice' => false,
            'shippingTax' => 115,
            'shippingTaxIncludedInPrice' => false,
            'refundAmount' => 2200,
            'expectedItemAmounts' => [970, 1230],
            'expectedShipmentAmount' => null,
        ];

        yield 'tax excluded: refund equals items with tax total' => [
            'itemTaxPerUnit' => 230,
            'itemTaxIncludedInPrice' => false,
            'shippingTax' => 115,
            'shippingTaxIncludedInPrice' => false,
            'refundAmount' => 2460,
            'expectedItemAmounts' => [1230, 1230],
            'expectedShipmentAmount' => null,
        ];

        yield 'tax excluded: refund exceeds items, partial shipping' => [
            'itemTaxPerUnit' => 230,
            'itemTaxIncludedInPrice' => false,
            'shippingTax' => 115,
            'shippingTaxIncludedInPrice' => false,
            'refundAmount' => 2600,
            'expectedItemAmounts' => [1230, 1230],
            'expectedShipmentAmount' => 140,
        ];

        yield 'tax excluded: refund below items plus base shipping' => [
            'itemTaxPerUnit' => 230,
            'itemTaxIncludedInPrice' => false,
            'shippingTax' => 115,
            'shippingTaxIncludedInPrice' => false,
            'refundAmount' => 2800,
            'expectedItemAmounts' => [1230, 1230],
            'expectedShipmentAmount' => 340,
        ];

        yield 'tax excluded: refund equals items plus base shipping' => [
            'itemTaxPerUnit' => 230,
            'itemTaxIncludedInPrice' => false,
            'shippingTax' => 115,
            'shippingTaxIncludedInPrice' => false,
            'refundAmount' => 2960,
            'expectedItemAmounts' => [1230, 1230],
            'expectedShipmentAmount' => 500,
        ];

        yield 'tax excluded: refund in shipping tax bug zone' => [
            'itemTaxPerUnit' => 230,
            'itemTaxIncludedInPrice' => false,
            'shippingTax' => 115,
            'shippingTaxIncludedInPrice' => false,
            'refundAmount' => 3000,
            'expectedItemAmounts' => [1230, 1230],
            'expectedShipmentAmount' => 540,
        ];

        yield 'item tax included, shipping tax excluded: refund spans two units' => [
            'itemTaxPerUnit' => 187,
            'itemTaxIncludedInPrice' => true,
            'shippingTax' => 115,
            'shippingTaxIncludedInPrice' => false,
            'refundAmount' => 1500,
            'expectedItemAmounts' => [500, 1000],
            'expectedShipmentAmount' => null,
        ];

        yield 'item tax included, shipping tax excluded: refund equals items total' => [
            'itemTaxPerUnit' => 187,
            'itemTaxIncludedInPrice' => true,
            'shippingTax' => 115,
            'shippingTaxIncludedInPrice' => false,
            'refundAmount' => 2000,
            'expectedItemAmounts' => [1000, 1000],
            'expectedShipmentAmount' => null,
        ];

        yield 'item tax included, shipping tax excluded: refund into shipping' => [
            'itemTaxPerUnit' => 187,
            'itemTaxIncludedInPrice' => true,
            'shippingTax' => 115,
            'shippingTaxIncludedInPrice' => false,
            'refundAmount' => 2200,
            'expectedItemAmounts' => [1000, 1000],
            'expectedShipmentAmount' => 200,
        ];

        yield 'item tax included, shipping tax excluded: refund equals items plus base shipping' => [
            'itemTaxPerUnit' => 187,
            'itemTaxIncludedInPrice' => true,
            'shippingTax' => 115,
            'shippingTaxIncludedInPrice' => false,
            'refundAmount' => 2500,
            'expectedItemAmounts' => [1000, 1000],
            'expectedShipmentAmount' => 500,
        ];

        yield 'item tax included, shipping tax excluded: refund in shipping tax bug zone' => [
            'itemTaxPerUnit' => 187,
            'itemTaxIncludedInPrice' => true,
            'shippingTax' => 115,
            'shippingTaxIncludedInPrice' => false,
            'refundAmount' => 2550,
            'expectedItemAmounts' => [1000, 1000],
            'expectedShipmentAmount' => 550,
        ];

        yield 'item tax excluded, shipping tax included: refund spans two units' => [
            'itemTaxPerUnit' => 230,
            'itemTaxIncludedInPrice' => false,
            'shippingTax' => 96,
            'shippingTaxIncludedInPrice' => true,
            'refundAmount' => 1500,
            'expectedItemAmounts' => [270, 1230],
            'expectedShipmentAmount' => null,
        ];

        yield 'item tax excluded, shipping tax included: refund into shipping' => [
            'itemTaxPerUnit' => 230,
            'itemTaxIncludedInPrice' => false,
            'shippingTax' => 96,
            'shippingTaxIncludedInPrice' => true,
            'refundAmount' => 2600,
            'expectedItemAmounts' => [1230, 1230],
            'expectedShipmentAmount' => 140,
        ];

        yield 'item tax excluded, shipping tax included: refund equals full total' => [
            'itemTaxPerUnit' => 230,
            'itemTaxIncludedInPrice' => false,
            'shippingTax' => 96,
            'shippingTaxIncludedInPrice' => true,
            'refundAmount' => 2960,
            'expectedItemAmounts' => [1230, 1230],
            'expectedShipmentAmount' => 500,
        ];

        yield 'both taxes included: refund spans two units' => [
            'itemTaxPerUnit' => 187,
            'itemTaxIncludedInPrice' => true,
            'shippingTax' => 96,
            'shippingTaxIncludedInPrice' => true,
            'refundAmount' => 1500,
            'expectedItemAmounts' => [500, 1000],
            'expectedShipmentAmount' => null,
        ];

        yield 'both taxes included: refund into shipping' => [
            'itemTaxPerUnit' => 187,
            'itemTaxIncludedInPrice' => true,
            'shippingTax' => 96,
            'shippingTaxIncludedInPrice' => true,
            'refundAmount' => 2300,
            'expectedItemAmounts' => [1000, 1000],
            'expectedShipmentAmount' => 300,
        ];

        yield 'both taxes included: refund equals full total' => [
            'itemTaxPerUnit' => 187,
            'itemTaxIncludedInPrice' => true,
            'shippingTax' => 96,
            'shippingTaxIncludedInPrice' => true,
            'refundAmount' => 2500,
            'expectedItemAmounts' => [1000, 1000],
            'expectedShipmentAmount' => 500,
        ];
    }

    private function createOrder(
        int $itemUnitPrice,
        int $itemQuantity,
        int $itemTaxPerUnit = 0,
        bool $itemTaxIncludedInPrice = false,
        int $shippingAmount = 0,
        int $shippingTax = 0,
        bool $shippingTaxIncludedInPrice = false,
        ?int $refundAmount = null,
    ): MolliePayment {
        ++$this->orderCounter;
        $orderNumber = sprintf('REFUND_TEST_%03d', $this->orderCounter);

        /** @var ChannelInterface $channel */
        $channel = $this->entityManager->find(ChannelInterface::class, $this->channelId);
        /** @var CustomerInterface $customer */
        $customer = $this->entityManager->find(CustomerInterface::class, $this->customerId);
        /** @var ProductVariantInterface $variant */
        $variant = $this->entityManager->find(ProductVariantInterface::class, $this->variantId);
        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $this->entityManager->find(PaymentMethodInterface::class, $this->paymentMethodId);

        $order = new Order();
        $order->setCustomer($customer);
        $order->setCurrencyCode('EUR');
        $order->setLocaleCode('en_US');
        $order->setChannel($channel);
        $order->setTokenValue('token_' . $this->orderCounter);
        $order->setNumber($orderNumber);
        $order->setState(OrderInterface::STATE_NEW);
        $order->setCheckoutState(OrderCheckoutStates::STATE_COMPLETED);
        $order->setPaymentState(OrderPaymentStates::STATE_PAID);
        $order->setCheckoutCompletedAt(new \DateTime());

        $orderItem = new OrderItem();
        $orderItem->setVariant($variant);
        $orderItem->setUnitPrice($itemUnitPrice);
        $order->addItem($orderItem);

        $units = [];
        for ($i = 0; $i < $itemQuantity; ++$i) {
            $unit = new OrderItemUnit($orderItem);
            $units[] = $unit;
        }

        if ($itemTaxPerUnit > 0) {
            foreach ($units as $unit) {
                $taxAdjustment = new Adjustment();
                $taxAdjustment->setType(AdjustmentInterface::TAX_ADJUSTMENT);
                $taxAdjustment->setAmount($itemTaxPerUnit);
                $taxAdjustment->setNeutral($itemTaxIncludedInPrice);
                $taxAdjustment->setLabel('Tax');
                $unit->addAdjustment($taxAdjustment);
            }
        }

        if ($shippingAmount > 0) {
            /** @var ShippingMethod $shippingMethod */
            $shippingMethod = $this->entityManager->find(ShippingMethod::class, $this->shippingMethodId);

            $shipment = new Shipment();
            $shipment->setMethod($shippingMethod);
            $shipment->setOrder($order);
            $order->addShipment($shipment);

            $shippingAdjustment = new Adjustment();
            $shippingAdjustment->setType(AdjustmentInterface::SHIPPING_ADJUSTMENT);
            $shippingAdjustment->setAmount($shippingAmount);
            $shippingAdjustment->setLabel('Shipping');
            $shipment->addAdjustment($shippingAdjustment);

            if ($shippingTax > 0) {
                $shippingTaxAdjustment = new Adjustment();
                $shippingTaxAdjustment->setType(AdjustmentInterface::TAX_ADJUSTMENT);
                $shippingTaxAdjustment->setAmount($shippingTax);
                $shippingTaxAdjustment->setNeutral($shippingTaxIncludedInPrice);
                $shippingTaxAdjustment->setLabel('Shipping Tax');
                $shipment->addAdjustment($shippingTaxAdjustment);
            }
        }

        $order->recalculateItemsTotal();

        $orderTotal = $order->getTotal();

        $payment = new Payment();
        $payment->setMethod($paymentMethod);
        $payment->setOrder($order);
        $payment->setCurrencyCode('EUR');
        $payment->setAmount($orderTotal);
        $payment->setState('completed');

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        return $this->createMolliePayment($order->getId(), $refundAmount ?? $orderTotal);
    }

    private function createMolliePayment(int $orderId, int $refundAmount): MolliePayment
    {
        $payment = new MolliePayment($this->mollieApiClient);
        $payment->metadata = (object) ['order_id' => $orderId];
        $payment->amountRefunded = (object) [
            'value' => number_format($refundAmount / 100, 2, '.', ''),
        ];

        return $payment;
    }

    /**
     * @param int[] $expectedItemAmounts
     */
    private function assertRefundUnits(
        RefundUnits $command,
        array $expectedItemAmounts,
        ?int $expectedShipmentAmount,
    ): void {
        $units = $command->units();

        $actualItemAmounts = [];
        $actualShipmentAmount = null;

        foreach ($units as $unit) {
            if ($unit instanceof OrderItemUnitRefund) {
                $actualItemAmounts[] = $unit->total();
            } elseif ($unit instanceof ShipmentRefund) {
                self::assertNull($actualShipmentAmount, 'Multiple ShipmentRefund units found, expected at most one.');
                $actualShipmentAmount = $unit->total();
            } else {
                self::fail('Unexpected unit type: ' . get_class($unit));
            }
        }

        sort($actualItemAmounts);
        sort($expectedItemAmounts);

        self::assertSame($expectedItemAmounts, $actualItemAmounts, 'OrderItemUnitRefund amounts do not match.');
        self::assertSame($expectedShipmentAmount, $actualShipmentAmount, 'ShipmentRefund amount does not match.');
    }
}
