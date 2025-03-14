<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Resolver;

use Mollie\Api\Resources\Method;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\MolliePlugin\Helper\IntToStringConverterInterface;

final class MollieAllowedMethodsResolver implements MollieAllowedMethodsResolverInterface
{
    /** @var MollieApiClientKeyResolverInterface */
    private $mollieApiClientKeyResolver;

    /** @var PaymentLocaleResolverInterface */
    private $paymentLocaleResolver;

    /** @var IntToStringConverterInterface */
    private $intToStringConverter;

    public function __construct(
        MollieApiClientKeyResolverInterface $mollieApiClientKeyResolver,
        PaymentLocaleResolverInterface $paymentLocaleResolver,
        IntToStringConverterInterface $intToStringConverter
    ) {
        $this->mollieApiClientKeyResolver = $mollieApiClientKeyResolver;
        $this->paymentLocaleResolver = $paymentLocaleResolver;
        $this->intToStringConverter = $intToStringConverter;
    }

    public function resolve(OrderInterface $order): array
    {
        $allowedMethodsIds = [];

        $client = $this->mollieApiClientKeyResolver->getClientWithKey();

        /** API will return only payment methods allowed for order total, currency, billing country */
        $allowedMethods = $client->methods->allActive($this->createParametersByOrder($order));

        /** @var Method $method */
        foreach ($allowedMethods as $method) {
            $allowedMethodsIds[] = $method->id;
        }

        return $allowedMethodsIds;
    }

    private function createParametersByOrder(OrderInterface $order): array
    {
        $parameters = array_merge(
            [
                'amount' => [
                    'value' => $this->intToStringConverter->convertIntToString($order->getTotal()),
                    'currency' => $order->getCurrencyCode(),
                ],
                'billingCountry' => null !== $order->getBillingAddress()
                    ? $order->getBillingAddress()->getCountryCode()
                    : null,
            ],
            MollieMethodsResolverInterface::PARAMETERS
        );

        if (null !== ($paymentLocale = $this->paymentLocaleResolver->resolveFromOrder($order))) {
            $parameters['locale'] = $paymentLocale;
        }

        return $parameters;
    }
}
