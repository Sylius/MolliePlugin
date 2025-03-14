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

namespace SyliusMolliePlugin\Provider\Apple;

use SyliusMolliePlugin\Client\MollieApiClient;
use SyliusMolliePlugin\Entity\OrderInterface;
use SyliusMolliePlugin\Provider\Order\OrderPaymentApplePayDirectProviderInterface;
use SyliusMolliePlugin\Resolver\Address\ApplePayAddressResolverInterface;
use Sylius\AdminOrderCreationPlugin\Provider\CustomerProviderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Component\HttpFoundation\Request;
use Webmozart\Assert\Assert;

final class ApplePayDirectProvider implements ApplePayDirectProviderInterface
{
    /** @var ApplePayAddressResolverInterface */
    private $applePayAddressResolver;

    /** @var MollieApiClient */
    private $mollieApiClient;

    /** @var OrderPaymentApplePayDirectProviderInterface */
    private $paymentApplePayDirectProvider;

    /** @var CustomerProviderInterface */
    private $customerProvider;

    /** @var ApplePayDirectPaymentProviderInterface */
    private $applePayDirectPaymentProvider;

    public function __construct(
        ApplePayAddressResolverInterface $applePayAddressResolver,
        MollieApiClient $mollieApiClient,
        OrderPaymentApplePayDirectProviderInterface $paymentApplePayDirectProvider,
        CustomerProviderInterface $customerProvider,
        ApplePayDirectPaymentProviderInterface $applePayDirectPaymentProvider
    ) {
        $this->applePayAddressResolver = $applePayAddressResolver;
        $this->mollieApiClient = $mollieApiClient;
        $this->paymentApplePayDirectProvider = $paymentApplePayDirectProvider;
        $this->customerProvider = $customerProvider;
        $this->applePayDirectPaymentProvider = $applePayDirectPaymentProvider;
    }

    public function provideOrder(OrderInterface $order, Request $request): void
    {
        $applePayPaymentToken = $request->get('token');
        $applePayAddress = [];
        $applePayAddress['shippingContact'] = $request->get('shippingContact');
        $applePayAddress['billingContact'] = $request->get('billingContact');

        Assert::notNull($applePayPaymentToken);
        Assert::notNull($applePayAddress['shippingContact']);
        Assert::notNull($applePayAddress['billingContact']);

        if (isset($applePayAddress['shippingContact']['emailAddress'])) {
            $applePayAddress['billingContact']['emailAddress'] = $applePayAddress['shippingContact']['emailAddress'];
        }

        $this->applePayAddressResolver->resolve($order, $applePayAddress);
        $this->paymentApplePayDirectProvider->provideOrderPayment($order, PaymentInterface::STATE_NEW);

        Assert::notNull($order->getShippingAddress());
        if (null !== $customer = $order->getShippingAddress()->getCustomer()) {
            $order->setCustomer($customer);
        }

        if (null === $order->getCustomer()) {
            $this->customerProvider->provideNewCustomer($applePayAddress['shippingContact']['emailAddress']);
        }

        $this->applePayDirectPaymentProvider->provideApplePayPayment($order, $applePayPaymentToken);
    }
}
