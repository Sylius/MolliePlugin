<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\EventListener;

use Sylius\MolliePlugin\Factory\MollieGatewayFactory;
use Sylius\MolliePlugin\Repository\PaymentMethodRepositoryInterface;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\RefundPlugin\Entity\RefundPaymentInterface;
use Sylius\RefundPlugin\Event\RefundPaymentGenerated;
use Sylius\RefundPlugin\StateResolver\RefundPaymentCompletedStateApplierInterface;
use Webmozart\Assert\Assert;

final class RefundPaymentGeneratedAutoCompleteListener
{
    /** @var EntityRepository */
    private $refundPaymentRepository;

    /** @var RefundPaymentCompletedStateApplierInterface */
    private $refundPaymentCompletedStateApplier;

    /** @var PaymentMethodRepositoryInterface */
    private $paymentMethodRepository;

    public function __construct(
        EntityRepository $refundPaymentInterface,
        RefundPaymentCompletedStateApplierInterface $refundPaymentCompletedStateApplier,
        PaymentMethodRepositoryInterface $paymentMethodRepository
    ) {
        $this->refundPaymentRepository = $refundPaymentInterface;
        $this->refundPaymentCompletedStateApplier = $refundPaymentCompletedStateApplier;
        $this->paymentMethodRepository = $paymentMethodRepository;
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
