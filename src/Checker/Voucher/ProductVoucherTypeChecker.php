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

namespace SyliusMolliePlugin\Checker\Voucher;

use SyliusMolliePlugin\Entity\MollieGatewayConfigInterface;
use SyliusMolliePlugin\Entity\ProductInterface;
use SyliusMolliePlugin\Entity\ProductType;
use SyliusMolliePlugin\Payments\Methods\MealVoucher;
use SyliusMolliePlugin\Repository\MollieGatewayConfigRepository;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Webmozart\Assert\Assert;

final class ProductVoucherTypeChecker implements ProductVoucherTypeCheckerInterface
{

    public function __construct(private MollieGatewayConfigRepository $paymentMethodRepository)
    {
    }

    public function checkTheProductTypeOnCart(OrderInterface $order, array $methods): array
    {
        if (false === $this->checkVoucherEnabled($methods)) {
            return $methods;
        }

        /** @var ?MollieGatewayConfigInterface $mealVoucher */
        $mealVoucher = $this->paymentMethodRepository->findOneBy(['methodId' => MealVoucher::MEAL_VOUCHERS]);
        Assert::isInstanceOf($mealVoucher, MollieGatewayConfigInterface::class);

        if ($mealVoucher->getDefaultCategory() instanceof ProductType) {
            return $methods;
        }

        $items = $order->getItems();
        /** @var OrderItemInterface $item */
        foreach ($items as $item) {
            /** @var ProductInterface $product */
            $product = $item->getProduct();
            if (null === $product->getProductType()) {
                $key = array_search(MealVoucher::MEAL_VOUCHERS, $methods['data'], true);
                unset($methods['data'][$key]);
            }
        }

        return $methods;
    }

    private function checkVoucherEnabled(array $methods): bool
    {
        return in_array(MealVoucher::MEAL_VOUCHERS, array_values($methods['data']), true);
    }
}
