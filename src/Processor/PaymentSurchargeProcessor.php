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

namespace Sylius\MolliePlugin\Processor;

use Doctrine\Common\Collections\Collection;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Sylius\MolliePlugin\Calculator\PaymentFee\PaymentSurchargeCalculatorInterface;
use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\MolliePlugin\Entity\MollieGatewayConfig;
use Webmozart\Assert\Assert;
use Sylius\MolliePlugin\Model\AdjustmentInterface as MollieAdjustmentInterface;

final class PaymentSurchargeProcessor implements PaymentSurchargeProcessorInterface, OrderProcessorInterface
{
    public function __construct(private readonly PaymentSurchargeCalculatorInterface $calculator)
    {
    }

    public function process(OrderInterface $order): void
    {
        /** @var PaymentInterface $payment */
        $payment = $order->getPayments()->first();

        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $payment->getMethod();

        Assert::notNull($paymentMethod->getGatewayConfig());
        if ('mollie' !== $paymentMethod->getGatewayConfig()->getFactoryName()) {
            $this->removeMollieAdjustments($order);
            return;
        }

        $data = $payment->getDetails();

        if (0 === count($data)) {
            return;
        }

        $molliePaymentMethod = $data['molliePaymentMethods'];

        $paymentSurcharge = $this->getMolliePaymentSurcharge($paymentMethod, $molliePaymentMethod);

        if (null === $paymentSurcharge) {
            return;
        }

        $this->calculator->calculate($order, $paymentSurcharge);
    }

    private function getMolliePaymentSurcharge(
        PaymentMethodInterface $paymentMethod,
        ?string $molliePaymentMethod = null,
    ): ?MollieGatewayConfig {
        /** @var GatewayConfigInterface $gatewayConfig */
        $gatewayConfig = $paymentMethod->getGatewayConfig();
        /** @var Collection $configMethods */
        $configMethods = $gatewayConfig->getMollieGatewayConfig();

        if (null === $molliePaymentMethod) {
            return $configMethods->last();
        }

        foreach ($configMethods as $configMethod) {
            /** @var MollieGatewayConfig $configMethod */
            if ($configMethod->getMethodId() === $molliePaymentMethod) {
                return $configMethod;
            }
        }

        return null;
    }

    private function removeMollieAdjustments(OrderInterface $order): void
    {
        foreach ($this->getMollieAdjustmentTypes() as $type) {
            foreach ($order->getAdjustmentsRecursively($type) as $adjustment) {
                $order->removeAdjustment($adjustment);
            }
        }
    }

    private function getMollieAdjustmentTypes(): array
    {
        return [
            MollieAdjustmentInterface::PERCENTAGE_ADJUSTMENT,
            MollieAdjustmentInterface::FIXED_AMOUNT_ADJUSTMENT,
            MollieAdjustmentInterface::PERCENTAGE_AND_AMOUNT_ADJUSTMENT,
        ];
    }
}
