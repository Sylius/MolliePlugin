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

namespace Sylius\MolliePlugin\ApplePay\Provider;

use Sylius\AdminOrderCreationPlugin\Provider\CustomerProviderInterface as OrderCreationCustomerProviderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\MolliePlugin\ApplePay\Resolver\AddressResolverInterface;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\MolliePlugin\Provider\CustomerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Webmozart\Assert\Assert;

final class ApplePayDirectProvider implements ApplePayDirectProviderInterface
{
    public function __construct(
        private readonly AddressResolverInterface $applePayAddressResolver,
        private readonly OrderPaymentApplePayDirectProviderInterface $paymentApplePayDirectProvider,
        private readonly CustomerProviderInterface|OrderCreationCustomerProviderInterface $customerProvider,
        private readonly ApplePayDirectPaymentProviderInterface $applePayDirectPaymentProvider,
    ) {
    }

    public function provideOrder(OrderInterface $order, Request $request): void
    {
        $applePayPaymentToken = $request->get('token');
        $applePayBillingAddress = $request->get('billingContact');
        $applePayShippingAddress = $request->get('shippingContact');

        Assert::notNull($applePayPaymentToken);
        Assert::notNull($applePayBillingAddress);
        Assert::notNull($applePayShippingAddress);

        if (isset($applePayShippingAddress['emailAddress'])) {
            $applePayBillingAddress['emailAddress'] = $applePayShippingAddress['emailAddress'];
        }

        $order->setShippingAddress($this->applePayAddressResolver->resolve($applePayShippingAddress));
        $order->setBillingAddress($this->applePayAddressResolver->resolve($applePayBillingAddress));

        $this->paymentApplePayDirectProvider->provideOrderPayment($order, PaymentInterface::STATE_NEW);

        if (null !== $customer = $order->getShippingAddress()->getCustomer()) {
            $order->setCustomer($customer);
        }

        if (null === $order->getCustomer()) {
            $this->customerProvider->provideNewCustomer($applePayShippingAddress['emailAddress']);
        }

        $this->applePayDirectPaymentProvider->provideApplePayPayment($order, $applePayPaymentToken);
    }
}
