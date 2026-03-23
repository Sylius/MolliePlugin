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

namespace Sylius\MolliePlugin\Creator;

use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\MolliePlugin\Converter\IntToStringConverterInterface;
use Sylius\MolliePlugin\Converter\OrderConverterInterface;
use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\MolliePlugin\Provider\DivisorProviderInterface;
use Sylius\MolliePlugin\Provider\PaymentDescriptionProviderInterface;
use Sylius\MolliePlugin\Repository\MollieGatewayConfigRepositoryInterface;
use Sylius\MolliePlugin\Resolver\PaymentLocaleResolverInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class PaymentDataCreator implements PaymentDataCreatorInterface
{
    public function __construct(
        private readonly IntToStringConverterInterface $intToStringConverter,
        private readonly UrlGeneratorInterface $router,
        private readonly PaymentDescriptionProviderInterface $paymentDescriptionProvider,
        private readonly PaymentLocaleResolverInterface $paymentLocaleResolver,
        private readonly DivisorProviderInterface $divisorProvider,
        private readonly MollieGatewayConfigRepositoryInterface $mollieGatewayConfigRepository,
        private readonly OrderConverterInterface $orderConverter,
        private readonly string $defaultLocaleCode,
    ) {
    }

    public function create(
        OrderInterface $order,
        PaymentInterface $payment,
        GatewayConfigInterface $gatewayConfig,
        string $methodId,
        bool $isSubscription,
        string $backUrl,
        ?string $customerMollieId,
    ): array {
        $divisor = $this->divisorProvider->getDivisor();

        $methodConfig = $this->mollieGatewayConfigRepository->findOneActiveByGatewayNameAndMethod(
            $gatewayConfig->getGatewayName(),
            $methodId,
        );
        if (null === $methodConfig) {
            throw new BadRequestHttpException(sprintf('Mollie method "%s" cannot be selected', $methodId));
        }

        $webhookUrl = $this->router->generate(
            'sylius_mollie_shop_payment_webhook',
            ['_locale' => $order->getLocaleCode() ?? $this->defaultLocaleCode],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );

        $paymentData = [
            'method' => $methodId,
            'amount' => [
                'currency' => $payment->getCurrencyCode(),
                'value' => $this->intToStringConverter->convertIntToString($payment->getAmount(), $divisor),
            ],
            'description' => $this->paymentDescriptionProvider->getPaymentDescription($payment, $methodConfig, $order),
            'redirectUrl' => $backUrl,
            'webhookUrl' => $webhookUrl,
            'metadata' => [
                'order_id' => $order->getId(),
                'customer_id' => $order->getCustomer()?->getId(),
                'molliePaymentMethods' => $methodId,
            ],
        ];

        $converted = $this->orderConverter->convert($order, $paymentData, $divisor, $methodConfig);
        $paymentData['billingAddress'] = $converted['billingAddress'];
        $paymentData['shippingAddress'] = $converted['shippingAddress'];
        $paymentData['lines'] = array_map(
            static fn (array $line): array => [
                'description' => $line['name'] ?? '',
                'type' => $line['type'] ?? 'physical',
                'quantity' => $line['quantity'],
                'unitPrice' => $line['unitPrice'],
                'totalAmount' => $line['totalAmount'],
                'vatRate' => $line['vatRate'],
                'vatAmount' => $line['vatAmount'],
            ],
            $converted['lines'],
        );

        $locale = $this->paymentLocaleResolver->resolveFromOrder($order);

        if ($locale !== null) {
            $paymentData['locale'] = $locale;
        }

        if ($isSubscription) {
            $paymentData['customerId'] = $customerMollieId;
            $paymentData['sequenceType'] = 'first';
            $paymentData['metadata']['sequenceType'] = 'first';
        }

        return $paymentData;
    }
}
