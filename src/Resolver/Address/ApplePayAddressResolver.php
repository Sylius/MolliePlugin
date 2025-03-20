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

namespace Sylius\MolliePlugin\Resolver\Address;

use Sylius\Component\Core\Model\OrderInterface;

final class ApplePayAddressResolver implements ApplePayAddressResolverInterface
{
    public function __construct(private readonly AddressResolverInterface $addressResolver)
    {
    }

    public function resolve(OrderInterface $order, array $applePayData): void
    {
        $appleShippingAddress = $this->addressResolver->resolve($applePayData['shippingContact']);
        $appleBillingAddress = $this->addressResolver->resolve($applePayData['billingContact']);

        try {
            $order->setShippingAddress($appleShippingAddress);
            $order->setBillingAddress($appleBillingAddress);
        } catch (\Exception) {
            throw new \Exception(\sprintf('Some error with create address to order'));
        }
    }
}
