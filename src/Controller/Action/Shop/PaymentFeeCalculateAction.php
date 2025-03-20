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

namespace Sylius\MolliePlugin\Controller\Action\Shop;

use Liip\ImagineBundle\Exception\Config\Filter\NotFoundException;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Order\Aggregator\AdjustmentsAggregatorInterface;
use Sylius\Component\Order\Context\CartContextInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\MolliePlugin\Entity\MollieGatewayConfig;
use Sylius\MolliePlugin\Helper\ConvertPriceToAmount;
use Sylius\MolliePlugin\PaymentFee\Calculator\PaymentSurchargeCalculatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

final class PaymentFeeCalculateAction implements PaymentFeeCalculateActionInterface
{
    public function __construct(
        private readonly PaymentSurchargeCalculatorInterface $paymentSurchargeCalculator,
        private readonly CartContextInterface $cartContext,
        private readonly RepositoryInterface $methodRepository,
        private readonly AdjustmentsAggregatorInterface $adjustmentsAggregator,
        private readonly ConvertPriceToAmount $convertPriceToAmount,
        private readonly Environment $twig,
    ) {
    }

    public function __invoke(Request $request, string $methodId): Response
    {
        /** @var OrderInterface $order */
        $order = $this->cartContext->getCart();
        $method = $this->methodRepository->findOneBy(['methodId' => $methodId]);

        if (!$method instanceof MollieGatewayConfig) {
            throw new NotFoundException(sprintf('Method with id %s not found', $methodId));
        }

        $this->paymentSurchargeCalculator->calculate($order, $method);

        $paymentFee = $this->getPaymentFee($order);

        if (0 === count($paymentFee)) {
            return new JsonResponse([], Response::HTTP_OK);
        }

        return new JsonResponse([
            'view' => $this->twig->render(
                'SyliusMolliePlugin:Shop/PaymentMollie:_paymentFeeTableTr.html.twig',
                [
                    'paymentFee' => $this->convertPriceToAmount->convert(reset($paymentFee)),
                ],
            ),
            'orderTotal' => $this->convertPriceToAmount->convert($order->getTotal()),
        ]);
    }

    private function getPaymentFee(OrderInterface $calculatedOrder): array
    {
        foreach (self::PAYMENTS_FEE_METHOD as $paymentFee) {
            $adjustmentsRecursively = $calculatedOrder->getAdjustmentsRecursively($paymentFee);
            if ($adjustmentsRecursively->isEmpty()) {
                continue;
            }

            return $this->adjustmentsAggregator->aggregate($adjustmentsRecursively);
        }

        return [];
    }
}
