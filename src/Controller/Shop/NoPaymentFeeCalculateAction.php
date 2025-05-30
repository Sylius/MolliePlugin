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

namespace Sylius\MolliePlugin\Controller\Shop;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Order\Context\CartContextInterface;
use Sylius\MolliePlugin\Calculator\Clearer\PaymentFeeAdjustmentClearerInterface;
use Sylius\MolliePlugin\Converter\PriceToAmountConverterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class NoPaymentFeeCalculateAction
{
    public function __construct(
        private CartContextInterface $cartContext,
        private PriceToAmountConverterInterface $priceToAmountConverter,
        private PaymentFeeAdjustmentClearerInterface $paymentFeeAdjustmentClearer,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        /** @var OrderInterface $order */
        $order = $this->cartContext->getCart();
        $this->paymentFeeAdjustmentClearer->clear($order);

        return new JsonResponse([
            'orderTotal' => $this->priceToAmountConverter->convert($order->getTotal()),
        ]);
    }
}
