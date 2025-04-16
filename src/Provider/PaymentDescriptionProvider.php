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

namespace Sylius\MolliePlugin\Provider;

use Mollie\Api\Types\PaymentMethod;
use Sylius\Bundle\PayumBundle\Provider\PaymentDescriptionProviderInterface as BasePaymentDescriptionProviderInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\MolliePlugin\Entity\MollieGatewayConfigInterface;
use Sylius\MolliePlugin\Model\ApiType;
use Webmozart\Assert\Assert;

final class PaymentDescriptionProvider implements PaymentDescriptionProviderInterface
{
    public function __construct(
        private readonly BasePaymentDescriptionProviderInterface $basePaymentDescriptionProvider,
    ) {
    }

    public function getPaymentDescription(
        PaymentInterface $payment,
        MollieGatewayConfigInterface $methodConfig,
        OrderInterface $order,
    ): string {
        $paymentMethodType = array_search($methodConfig->getPaymentType(), ApiType::getAllAvailable(), true);
        $description = $methodConfig->getPaymentDescription();

        if (PaymentMethod::PAYPAL === $methodConfig->getMethodId()) {
            Assert::notNull($order->getNumber());

            return sprintf('Order %s', $order->getNumber());
        }

        if (ApiType::PAYMENT_API === $paymentMethodType &&
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

        return $this->basePaymentDescriptionProvider->getPaymentDescription($payment);
    }
}
