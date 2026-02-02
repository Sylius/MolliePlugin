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

namespace Sylius\MolliePlugin\Api\Controller;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\MolliePlugin\Entity\PaymentSurchargeFeeInterface;
use Sylius\MolliePlugin\Payum\Checker\MollieGatewayFactoryCheckerInterface;
use Sylius\MolliePlugin\Resolver\MolliePaymentsMethodResolver;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class GetMollieMethodsAction
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly MollieGatewayFactoryCheckerInterface $mollieGatewayFactoryChecker,
        private readonly MolliePaymentsMethodResolver $molliePaymentsMethodResolver,
        private readonly CacheManager $imagineCacheManager,
    ) {
    }

    public function __invoke(string $tokenValue): JsonResponse
    {
        $order = $this->orderRepository->findOneByTokenValue($tokenValue);
        if (!$order instanceof OrderInterface) {
            throw new NotFoundHttpException(sprintf('Order with token "%s" not found', $tokenValue));
        }

        $payment = $order->getLastPayment();
        if (!$payment instanceof PaymentInterface) {
            throw new NotFoundHttpException('No payment found');
        }

        $paymentMethod = $payment->getMethod();
        if (!$paymentMethod instanceof PaymentMethodInterface) {
            throw new NotFoundHttpException('No payment method found');
        }

        $gatewayConfig = $paymentMethod->getGatewayConfig();
        if (!$gatewayConfig instanceof GatewayConfigInterface) {
            throw new NotFoundHttpException('No gateway config found');
        }
        if (!$this->mollieGatewayFactoryChecker->isMollieGateway($gatewayConfig)) {
            throw new BadRequestException('The payment method is not using Mollie');
        }

        $availableMethods = $this->molliePaymentsMethodResolver->resolve();

        $data = $availableMethods['data'];
        $images = $availableMethods['image'];
        $paymentFees = $availableMethods['paymentFee'];
        $result = [];
        foreach ($data as $id => $label) {
            $result[] = [
                'id' => $id,
                'label' => $label,
                'image' => $this->normalizeImage($images[$id] ?? null),
                'paymentFee' => $this->normalizePaymentFee($paymentFees[$id] ?? null),
            ];
        }

        return new JsonResponse($result);
    }

    /**
     * @return array{
     *     type: string|null,
     *     fixedAmount: float|null,
     *     percentage: float|null,
     *     surchargeLimit: float|null,
     *  }|null
     */
    private function normalizePaymentFee(mixed $fee): ?array
    {
        if ($fee instanceof PaymentSurchargeFeeInterface) {
            return [
                'type' => $fee->getType(),
                'fixedAmount' => $fee->getFixedAmount(),
                'percentage' => $fee->getPercentage(),
                'surchargeLimit' => $fee->getSurchargeLimit(),
            ];
        }

        return null;
    }

    private function normalizeImage(?string $image): ?string
    {
        if (null === $image || str_starts_with($image, 'https://') && str_contains($image, 'mollie.com')) {
            return $image;
        }

        return $this->imagineCacheManager->getBrowserPath($image, 'sylius_original');
    }
}
