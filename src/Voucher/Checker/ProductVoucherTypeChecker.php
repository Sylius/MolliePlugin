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

namespace Sylius\MolliePlugin\Voucher\Checker;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\MolliePlugin\Entity\MollieGatewayConfigInterface;
use Sylius\MolliePlugin\Entity\ProductInterface;
use Sylius\MolliePlugin\Entity\ProductType;
use Sylius\MolliePlugin\Model\PaymentMethod\MealVoucher;
use Sylius\MolliePlugin\Repository\MollieGatewayConfigRepository;
use Webmozart\Assert\Assert;

final class ProductVoucherTypeChecker implements ProductVoucherTypeCheckerInterface
{
    public function __construct(private readonly MollieGatewayConfigRepository $paymentMethodRepository)
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

    /**
     * @param array{
     *      data: array<string, string>,
     *      image: array<string, string>,
     *      issuers: array<string, mixed>|null,
     *      paymentFee: array<string, mixed>
     *  } $methods
     */
    private function checkVoucherEnabled(array $methods): bool
    {
        return in_array(MealVoucher::MEAL_VOUCHERS, array_values($methods['data']), true);
    }
}
