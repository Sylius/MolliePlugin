<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\PaymentFee\Types;

use Sylius\MolliePlugin\Entity\MollieGatewayConfig;
use Sylius\MolliePlugin\Order\AdjustmentInterface;
use Sylius\MolliePlugin\Payments\PaymentTerms\Options;
use Sylius\Component\Order\Factory\AdjustmentFactoryInterface;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\MolliePlugin\Provider\Divisor\DivisorProviderInterface;
use Webmozart\Assert\Assert;

final class FixedAmount implements SurchargeTypeInterface
{
    /** @var AdjustmentFactoryInterface */
    private $adjustmentFactory;

    /** @var DivisorProviderInterface */
    private $divisorProvider;

    public function __construct(
        AdjustmentFactoryInterface $adjustmentFactory,
        DivisorProviderInterface $divisorProvider
    ) {
        $this->adjustmentFactory = $adjustmentFactory;
        $this->divisorProvider = $divisorProvider;
    }

    public function calculate(OrderInterface $order, MollieGatewayConfig $paymentMethod): OrderInterface
    {
        Assert::notNull($paymentMethod->getPaymentSurchargeFee());
        $fixedAmount = $paymentMethod->getPaymentSurchargeFee()->getFixedAmount();

        if (false === $order->getAdjustments(AdjustmentInterface::FIXED_AMOUNT_ADJUSTMENT)->isEmpty()) {
            $order->removeAdjustments(AdjustmentInterface::FIXED_AMOUNT_ADJUSTMENT);
        }

        /** @var AdjustmentInterface $adjustment */
        $adjustment = $this->adjustmentFactory->createNew();
        $adjustment->setType(AdjustmentInterface::FIXED_AMOUNT_ADJUSTMENT);
        Assert::notNull($fixedAmount);
        $adjustment->setAmount((int) ($fixedAmount * $this->divisorProvider->getDivisor()));
        $adjustment->setNeutral(false);

        $order->addAdjustment($adjustment);

        return $order;
    }

    public function canCalculate(string $type): bool
    {
        return Options::FIXED_FEE === array_search($type, Options::getAvailablePaymentSurchargeFeeType(), true);
    }
}
