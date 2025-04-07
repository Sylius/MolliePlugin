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

namespace Sylius\MolliePlugin\Resolver;

use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\MolliePlugin\Entity\MollieGatewayConfigInterface;
use Sylius\MolliePlugin\Entity\ProductInterface;
use Sylius\MolliePlugin\Model\PaymentMethod\MealVoucher;
use Webmozart\Assert\Assert;

final class MealVoucherResolver implements MealVoucherResolverInterface
{
    public function resolve(MollieGatewayConfigInterface $method, OrderItemInterface $item): ?string
    {
        if (MealVoucher::MEAL_VOUCHERS === $method->getMethodId()) {
            return $this->getMealVoucherCategory($method, $item);
        }

        return null;
    }

    private function getMealVoucherCategory(MollieGatewayConfigInterface $method, OrderItemInterface $item): ?string
    {
        if (null !== $this->getMealVoucherFromItem($item)) {
            return $this->getMealVoucherFromItem($item);
        }
        if (null !== $method->getDefaultCategory()) {
            return $method->getDefaultCategory()->getName();
        }
        Assert::notNull($item->getProduct());

        throw new \LogicException(\sprintf('Voucher need default category, product category found in product name %s', $item->getProduct()->getName()));
    }

    private function getMealVoucherFromItem(OrderItemInterface $item): ?string
    {
        /** @var ProductInterface $product */
        $product = $item->getProduct();

        return null === $product->getProductType() ? null : $product->getProductType()->getName();
    }
}
