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

namespace Sylius\MolliePlugin\Calculator\PaymentFee;

use Sylius\Component\Order\Model\OrderInterface;
use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\MolliePlugin\Entity\MollieGatewayConfig;
use Sylius\MolliePlugin\Provider\PaymentSurchargeAdjustmentsProviderInterface;
use Sylius\MolliePlugin\Repository\MollieGatewayConfigRepositoryInterface;

final readonly class ChargedSurchargeMatcher implements ChargedSurchargeMatcherInterface
{
    public function __construct(
        private PaymentSurchargeAdjustmentsProviderInterface $surchargeAdjustmentsProvider,
        private PaymentSurchargeAmountCalculatorInterface $surchargeAmountCalculator,
        private MollieGatewayConfigRepositoryInterface $mollieGatewayConfigRepository,
    ) {
    }

    public function chargedSurcharge(OrderInterface $order): int
    {
        $total = 0;

        foreach ($this->surchargeAdjustmentsProvider->getTypes() as $type) {
            foreach ($order->getAdjustments($type) as $adjustment) {
                $total += $adjustment->getAmount();
            }
        }

        return $total;
    }

    public function matches(OrderInterface $order, MollieGatewayConfig $config): bool
    {
        return $this->surchargeAmountCalculator->calculateAmount($order, $config) === $this->chargedSurcharge($order);
    }

    public function gatewayKeepsTheTotal(OrderInterface $order, GatewayConfigInterface $gateway): bool
    {
        foreach ($this->enabledConfigs($gateway) as $config) {
            if ($this->matches($order, $config)) {
                return true;
            }
        }

        return false;
    }

    /**
     * `findAllEnabledByGateway()` selects the amount limits alongside the entity, so Doctrine hands
     * back rows shaped `[0 => MollieGatewayConfig, 'minimumAmount' => …, 'maximumAmount' => …]`
     * rather than the entities its return type advertises.
     *
     * @return MollieGatewayConfig[]
     */
    private function enabledConfigs(GatewayConfigInterface $gateway): array
    {
        /** @var array<array-key, mixed> $rows */
        $rows = $this->mollieGatewayConfigRepository->findAllEnabledByGateway($gateway);

        $configs = [];

        foreach ($rows as $row) {
            $config = is_array($row) ? ($row[0] ?? null) : $row;

            if ($config instanceof MollieGatewayConfig) {
                $configs[] = $config;
            }
        }

        return $configs;
    }
}
