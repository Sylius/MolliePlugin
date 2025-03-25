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

namespace Sylius\MolliePlugin\Resolver\ApplePayDirect;

use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\MolliePlugin\Entity\MollieGatewayConfigInterface;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\MolliePlugin\Helper\IntToStringConverterInterface;
use Sylius\MolliePlugin\Payments\Methods\AbstractMethod;

final class ApplePayDirectPaymentTypeResolver implements ApplePayDirectPaymentTypeResolverInterface
{
    public function __construct(private readonly ApplePayDirectApiPaymentResolverInterface $apiPaymentResolver, private readonly ApplePayDirectApiOrderPaymentResolverInterface $apiOrderPaymentResolver, private readonly IntToStringConverterInterface $intToStringConverter)
    {
    }

    public function resolve(
        MollieGatewayConfigInterface $mollieGatewayConfig,
        PaymentInterface $payment,
        array $applePayDirectToken,
    ): void {
        $details = [];
        /** @var OrderInterface $order */
        $order = $payment->getOrder();

        if (null === $payment->getAmount()) {
            return;
        }

        $amount = $this->intToStringConverter->convertIntToString($payment->getAmount());

        $details['amount'] = [
            'currency' => $payment->getCurrencyCode(),
            'value' => "$amount",
        ];

        $details['applePayDirectToken'] = json_encode($applePayDirectToken);
        $details['backurl'] = $payment->getDetails()['backurl'];

        if (AbstractMethod::ORDER_API === $mollieGatewayConfig->getPaymentType()) {
            $this->createPaymentOrder($order, $mollieGatewayConfig, $details);

            return;
        }

        $this->createPayment($order, $details);
    }

    /** @param array<string, mixed> $details */
    private function createPayment(OrderInterface $order, array $details): void
    {
        $this->apiPaymentResolver->resolve($order, $details);
    }

    /** @param array<string, mixed> $details */
    private function createPaymentOrder(
        OrderInterface $order,
        MollieGatewayConfigInterface $mollieGatewayConfig,
        array $details,
    ): void {
        $this->apiOrderPaymentResolver->resolve($order, $mollieGatewayConfig, $details);
    }
}
