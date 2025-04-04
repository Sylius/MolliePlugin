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

namespace Sylius\MolliePlugin\Converter;

use Mollie\Api\Types\PaymentMethod;
use Sylius\Component\Addressing\Matcher\ZoneMatcherInterface;
use Sylius\Component\Addressing\Model\ZoneInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItem;
use Sylius\Component\Core\Model\Scope;
use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\Component\Core\Model\ShippingMethodInterface;
use Sylius\Component\Customer\Model\CustomerInterface;
use Sylius\Component\Order\Model\Adjustment;
use Sylius\Component\Taxation\Model\TaxRateInterface;
use Sylius\Component\Taxation\Resolver\TaxRateResolverInterface;
use Sylius\MolliePlugin\Calculator\CalculateTaxAmountInterface;
use Sylius\MolliePlugin\Entity\MollieGatewayConfigInterface;
use Sylius\MolliePlugin\Model\DTO\MolliePayment\Amount;
use Sylius\MolliePlugin\Model\PaymentSurchargeFeeType;
use Sylius\MolliePlugin\Resolver\MealVoucherResolverInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Webmozart\Assert\Assert;

/** @phpstan-import-type AmountArray from Amount */
final class OrderConverter implements OrderConverterInterface
{
    private OrderInterface $order;

    private ZoneInterface $zone;

    public function __construct(
        private readonly IntToStringConverterInterface $intToStringConverter,
        private readonly CalculateTaxAmountInterface $calculateTaxAmount,
        private readonly MealVoucherResolverInterface $mealVoucherResolver,
        private readonly TaxRateResolverInterface $taxRateResolver,
        private readonly ZoneMatcherInterface $zoneMatcher,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function convert(
        OrderInterface $order,
        array $details,
        int $divisor,
        MollieGatewayConfigInterface $method,
    ): array {
        $this->order = $order;

        Assert::notNull($this->order->getBillingAddress());
        $this->zone = $this->zoneMatcher->match($this->order->getBillingAddress(), Scope::TAX);

        if (null === $this->zone && null !== $this->order->getChannel()) {
            Assert::notNull($this->order->getChannel());
            $this->zone = $this->order->getChannel()->getDefaultTaxZone();
        }

        $customer = $order->getCustomer();

        Assert::notNull($customer);
        $amount = $this->intToStringConverter->convertIntToString($order->getTotal(), $divisor);

        $details['amount']['value'] = $amount;
        $details['orderNumber'] = (string) $order->getNumber();
        $details['shippingAddress'] = $this->createShippingAddress($customer);
        $details['billingAddress'] = $this->createBillingAddress($customer, $method->getMethodId());
        $details['lines'] = $this->createLines($divisor, $method);
        $details['lines'] = array_merge($details['lines'], $this->createShippingFee($divisor));

        return $details;
    }

    /**
     * @return array{
     *     streetAndNumber: string,
     *     postalCode: string,
     *     city: string,
     *     country: string,
     *     givenName: string,
     *     familyName: string,
     *     organizationName: string,
     *     email: string,
     * }
     */
    private function createShippingAddress(CustomerInterface $customer): array
    {
        $shippingAddress = $this->order->getShippingAddress();

        Assert::notNull($shippingAddress);

        return [
            'streetAndNumber' => $shippingAddress->getStreet(),
            'postalCode' => $shippingAddress->getPostcode(),
            'city' => $shippingAddress->getCity(),
            'country' => $shippingAddress->getCountryCode(),
            'givenName' => $shippingAddress->getFirstName(),
            'familyName' => $shippingAddress->getLastName(),
            'organizationName' => $shippingAddress->getCompany(),
            'email' => $customer->getEmail(),
        ];
    }

    /**
     * @return array{
     *     streetAndNumber: string,
     *     postalCode: string,
     *     city: string,
     *     country: string,
     *     givenName: string,
     *     familyName: string,
     *     organizationName: string,
     *     email: string,
     *     phone?: string,
     * }
     */
    private function createBillingAddress(CustomerInterface $customer, string $methodId): array
    {
        $billingAddress = $this->order->getBillingAddress();

        Assert::notNull($billingAddress);

        $address = [
            'streetAndNumber' => $billingAddress->getStreet(),
            'postalCode' => $billingAddress->getPostcode(),
            'city' => $billingAddress->getCity(),
            'country' => $billingAddress->getCountryCode(),
            'givenName' => $billingAddress->getFirstName(),
            'familyName' => $billingAddress->getLastName(),
            'organizationName' => $billingAddress->getCompany(),
            'email' => $customer->getEmail(),
        ];

        if ($methodId === PaymentMethod::BANCOMATPAY && null !== $billingAddress->getPhoneNumber()) {
            $address['phone'] = $billingAddress->getPhoneNumber();
        }

        return $address;
    }

    /**
     * @return array<array-key, array{
     *     category?: string,
     *     type: string,
     *     name: string,
     *     quantity: int,
     *     vatRate: string,
     *     unitPrice: AmountArray,
     *     totalAmount: AmountArray,
     *     vatAmount: AmountArray,
     *     imageUrl?: string,
     *     discountAmount?: AmountArray,
     *     metadata?: array{item_id: int},
     * }>
     */
    private function createLines(int $divisor, MollieGatewayConfigInterface $method): array
    {
        $details = [];

        /** @var OrderItem $item */
        foreach ($this->order->getItems() as $item) {
            $taxRate = $this->taxRateResolver->resolve($item->getVariant(), [self::TAX_RATE_CRITERIA_ZONE => $this->zone]);
            $details[] = [
                'category' => $this->mealVoucherResolver->resolve($method, $item),
                'type' => 'physical',
                'name' => $item->getProductName(),
                'quantity' => $item->getQuantity(),
                'vatRate' => null === $taxRate ? '0.00' : (string) $taxRate->getAmountAsPercentage(),
                'unitPrice' => [
                    'currency' => $this->order->getCurrencyCode(),
                    'value' => $this->intToStringConverter->convertIntToString($this->getUnitPriceWithTax($item, $taxRate), $divisor),
                ],
                'totalAmount' => [
                    'currency' => $this->order->getCurrencyCode(),
                    'value' => $this->intToStringConverter->convertIntToString($item->getTotal(), $divisor),
                ],
                'vatAmount' => [
                    'currency' => $this->order->getCurrencyCode(),
                    'value' => null === $taxRate ?
                        '0.00' :
                        $this->calculateTaxAmount->calculate($taxRate->getAmount(), $item->getTotal()),
                ],
                'imageUrl' => $this->getImageUrl($item),
                'discountAmount' => [
                    'currency' => $this->order->getCurrencyCode(),
                    'value' => $this->intToStringConverter->convertIntToString($this->getItemDiscountAmount($item), $divisor),
                ],
                'metadata' => [
                    'item_id' => $item->getId(),
                ],
            ];
        }

        /** @var Adjustment $adjustment */
        foreach ($this->order->getAdjustments() as $adjustment) {
            if (in_array($adjustment->getType(), PaymentSurchargeFeeType::getAllAvailable(), true)) {
                $details[] = $this->createAdjustments($adjustment, $divisor);
            }
        }

        return $details;
    }

    /**
     * Fetches product image url
     */
    private function getImageUrl(OrderItem $item): string
    {
        $images = $item->getProduct()->getImages();
        $imagePaths = [];

        foreach ($images as $key => $image) {
            $imagePaths[] = $image->getPath();
        }

        if (!empty($imagePaths) && isset($imagePaths[0])) {
            return rtrim($this->fetchBaseShopUrl(), '/') . '/media/cache/sylius_shop_product_thumbnail/'
                . $imagePaths[0];
        }

        return '';
    }

    private function fetchBaseShopUrl(): string
    {
        return $this->requestStack->getCurrentRequest()->getSchemeAndHttpHost();
    }

    /**
     * @return array{
     *     type: string,
     *     name: string,
     *     quantity: int,
     *     vatRate: string,
     *     unitPrice: AmountArray,
     *     totalAmount: AmountArray,
     *     vatAmount: AmountArray,
     * }
     */
    private function createAdjustments(Adjustment $adjustment, int $divisor): array
    {
        return [
            'type' => self::PAYMENT_FEE_TYPE,
            'name' => self::PAYMENT_FEE,
            'quantity' => 1,
            'vatRate' => '0.00',
            'unitPrice' => [
                'currency' => $this->order->getCurrencyCode(),
                'value' => $this->intToStringConverter->convertIntToString($adjustment->getAmount(), $divisor),
            ],
            'totalAmount' => [
                'currency' => $this->order->getCurrencyCode(),
                'value' => $this->intToStringConverter->convertIntToString($adjustment->getAmount(), $divisor),
            ],
            'vatAmount' => [
                'currency' => $this->order->getCurrencyCode(),
                'value' => '0.00',
            ],
        ];
    }

    /**
     * @return array<array-key, array{
     *     type: string,
     *     name: string,
     *     quantity: int,
     *     vatRate: string,
     *     unitPrice: AmountArray,
     *     totalAmount: AmountArray,
     *     vatAmount: AmountArray,
     * }>
     */
    private function createShippingFee(int $divisor): array
    {
        $details = [];

        /** @var ShipmentInterface|bool $shipment */
        $shipment = $this->order->getShipments()->first();

        if (false !== $shipment) {
            /** @var ShippingMethodInterface $shipmentMethod */
            $shipmentMethod = $shipment->getMethod();
            $taxRate = $this->taxRateResolver->resolve($shipmentMethod, [self::TAX_RATE_CRITERIA_ZONE => $this->zone]);
            $details[] = [
                'type' => self::SHIPPING_TYPE,
                'name' => self::SHIPPING_FEE,
                'quantity' => 1,
                'vatRate' => null === $taxRate ? '0.00' : (string) $taxRate->getAmountAsPercentage(),
                'unitPrice' => [
                    'currency' => $this->order->getCurrencyCode(),
                    'value' => $this->intToStringConverter->convertIntToString($this->order->getShippingTotal(), $divisor),
                ],
                'totalAmount' => [
                    'currency' => $this->order->getCurrencyCode(),
                    'value' => $this->intToStringConverter->convertIntToString($this->order->getShippingTotal(), $divisor),
                ],
                'vatAmount' => [
                    'currency' => $this->order->getCurrencyCode(),
                    'value' => null === $taxRate ? '0.00' : $this->calculateTaxAmount->calculate($taxRate->getAmount(), $this->order->getShippingTotal()),
                ],
            ];
        }

        return $details;
    }

    private function getUnitPriceWithTax(OrderItem $item, ?TaxRateInterface $taxRate): int
    {
        if (null === $taxRate) {
            return $item->getUnitPrice();
        }

        if ($taxRate->isIncludedInPrice()) {
            return $item->getUnitPrice();
        }

        return (int) round($item->getUnitPrice() + ($item->getTaxTotal() / $item->getQuantity()));
    }

    private function getItemDiscountAmount(OrderItem $item): int
    {
        $totalDiscount = 0;

        foreach (self::ITEM_DISCOUNT_ADJUSTMENTS_TYPES as $adjustmentType) {
            $totalDiscount += $item->getAdjustmentsTotalRecursively($adjustmentType);
        }

        return $totalDiscount;
    }
}
