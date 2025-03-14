<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Resolver\Address;

use Sylius\Component\Core\Model\OrderInterface;

final class ApplePayAddressResolver implements ApplePayAddressResolverInterface
{
    /** @var AddressResolverInterface */
    private $addressResolver;

    public function __construct(AddressResolverInterface $addressResolver)
    {
        $this->addressResolver = $addressResolver;
    }

    public function resolve(OrderInterface $order, array $applePayData): void
    {
        $appleShippingAddress = $this->addressResolver->resolve($applePayData['shippingContact']);
        $appleBillingAddress = $this->addressResolver->resolve($applePayData['billingContact']);

        try {
            $order->setShippingAddress($appleShippingAddress);
            $order->setBillingAddress($appleBillingAddress);
        } catch (\Exception $e) {
            throw new \Exception(\sprintf('Some error with create address to order'));
        }
    }
}
