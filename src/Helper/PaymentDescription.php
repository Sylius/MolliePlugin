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

namespace Sylius\MolliePlugin\Helper;

use Mollie\Api\Types\PaymentMethod;
use Sylius\Bundle\PayumBundle\Provider\PaymentDescriptionProviderInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\MolliePlugin\Entity\MollieGatewayConfigInterface;
use Sylius\MolliePlugin\Payments\PaymentType;
use Webmozart\Assert\Assert;

final class PaymentDescription implements PaymentDescriptionInterface
{
    public function __construct(private readonly PaymentDescriptionProviderInterface $paymentDescriptionProvider)
    {
    }

    public function getPaymentDescription(
        PaymentInterface $payment,
        MollieGatewayConfigInterface $methodConfig,
        OrderInterface $order,
    ): string {
        $paymentMethodType = array_search($methodConfig->getPaymentType(), PaymentType::getAllAvailable(), true);
        $description = $methodConfig->getPaymentDescription();

        if (PaymentMethod::PAYPAL === $methodConfig->getMethodId()) {
            Assert::notNull($order->getNumber());

            return $this->createPayPalDescription($order->getNumber());
        }

        if (PaymentType::PAYMENT_API === $paymentMethodType &&
            isset($description) &&
            '' !== $description
        ) {
            Assert::notNull($order->getChannel());
            $replacements = [
                '{ordernumber}' => $order->getNumber(),
                '{storename}' => $order->getChannel()->getName(),
            ];

            Assert::notNull($methodConfig->getPaymentDescription());

            return str_replace(
                array_keys($replacements),
                array_values($replacements),
                $methodConfig->getPaymentDescription(),
            );
        }

        return $this->paymentDescriptionProvider->getPaymentDescription($payment);
    }

    private function createPayPalDescription(string $orderNumber): string
    {
        return sprintf('%s %s', self::PAYPAL_DESCRIPTION, $orderNumber);
    }
}
