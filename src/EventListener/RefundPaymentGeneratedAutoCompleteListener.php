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

namespace Sylius\MolliePlugin\EventListener;

use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\MolliePlugin\Payum\Factory\MollieGatewayFactory;
use Sylius\MolliePlugin\Repository\PaymentMethodRepositoryInterface;
use Sylius\RefundPlugin\Entity\RefundPaymentInterface;
use Sylius\RefundPlugin\Event\RefundPaymentGenerated;
use Sylius\RefundPlugin\StateResolver\RefundPaymentCompletedStateApplierInterface;
use Webmozart\Assert\Assert;

final class RefundPaymentGeneratedAutoCompleteListener
{
    public function __construct(
        private readonly EntityRepository $refundPaymentRepository,
        private readonly RefundPaymentCompletedStateApplierInterface $refundPaymentCompletedStateApplier,
        private readonly PaymentMethodRepositoryInterface $paymentMethodRepository,
    ) {
    }

    public function __invoke(RefundPaymentGenerated $refundPaymentGenerated): void
    {
        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $this->paymentMethodRepository->find($refundPaymentGenerated->paymentMethodId());

        Assert::notNull($paymentMethod->getGatewayConfig());
        if (MollieGatewayFactory::FACTORY_NAME !== $paymentMethod->getGatewayConfig()->getFactoryName()) {
            return;
        }

        /** @var RefundPaymentInterface $refundPayment */
        $refundPayment = $this->refundPaymentRepository->find($refundPaymentGenerated->id());

        $this->refundPaymentCompletedStateApplier->apply($refundPayment);
    }
}
