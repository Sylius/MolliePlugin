<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Refund\Generator;

use Sylius\MolliePlugin\DTO\PartialRefundItem;
use Sylius\MolliePlugin\DTO\PartialRefundItems;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\RefundPlugin\Entity\RefundInterface;
use Sylius\RefundPlugin\Model\RefundType;

final class PaymentRefundedGenerator implements PaymentRefundedGeneratorInterface
{
    /** @var RepositoryInterface */
    private $refundUnitsRepository;

    public function __construct(RepositoryInterface $refundUnitsRepository)
    {
        $this->refundUnitsRepository = $refundUnitsRepository;
    }

    public function generate(OrderInterface $order): PartialRefundItems
    {
        $refundedUnits = $this->refundUnitsRepository->findBy([
            'order' => $order->getId(),
            'type' => RefundType::orderItemUnit(),
        ]);

        $partialRefundItems = new PartialRefundItems();

        /** @var RefundInterface $refundedUnit */
        foreach ($refundedUnits as $refundedUnit) {
            $partialRefundItem = new PartialRefundItem();

            $partialRefund = $partialRefundItems->findById($refundedUnit->getRefundedUnitId());

            if (null !== $partialRefund) {
                $partialRefund->setAmountRefunded($refundedUnit->getAmount());

                continue;
            }

            $partialRefundItem->setId($refundedUnit->getRefundedUnitId());
            $partialRefundItem->setAmountRefunded($refundedUnit->getAmount());

            $partialRefundItems->setPartialRefundItems($partialRefundItem);
        }

        return $partialRefundItems;
    }
}
