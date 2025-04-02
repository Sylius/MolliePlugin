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

namespace Sylius\MolliePlugin\Refund\Handler;

use Payum\Core\Payum;
use Payum\Core\Request\Refund as RefundAction;
use Payum\Core\Security\TokenInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Resource\Exception\UpdateHandlingException;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\MolliePlugin\Logger\MollieLoggerActionInterface;
use Sylius\MolliePlugin\Payum\Factory\MollieGatewayFactory;
use Sylius\MolliePlugin\Payum\Factory\MollieSubscriptionGatewayFactory;
use Sylius\MolliePlugin\Payum\Request\Refund\RefundOrder;
use Sylius\RefundPlugin\Event\UnitsRefunded;
use Sylius\RefundPlugin\Filter\UnitRefundFilterInterface;
use Sylius\RefundPlugin\Model\OrderItemUnitRefund;
use Sylius\RefundPlugin\Model\ShipmentRefund;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Webmozart\Assert\Assert;

final class OrderPaymentRefund implements OrderPaymentRefundInterface
{
    public function __construct(
        private readonly RepositoryInterface $orderRepository,
        private readonly MollieLoggerActionInterface $loggerAction,
        private readonly Payum $payum,
        private readonly UnitRefundFilterInterface $unitRefundFilter,
    ) {
    }

    public function refund(UnitsRefunded $unitsRefunded): void
    {
        /** @var OrderInterface $order */
        $order = $this->orderRepository->findOneBy(['number' => $unitsRefunded->orderNumber()]);

        /** @var PaymentInterface|false|null $payment */
        $payment = $order->getPayments()->last();
        if (!$payment instanceof PaymentInterface) {
            $this->loggerAction->addNegativeLog('No payment in refund');

            throw new NotFoundHttpException();
        }

        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $payment->getMethod();

        $gatewayConfig = $paymentMethod->getGatewayConfig();
        Assert::notNull($gatewayConfig);

        $factoryName = $gatewayConfig->getFactoryName();
        if (false === in_array($factoryName, [MollieGatewayFactory::FACTORY_NAME, MollieSubscriptionGatewayFactory::FACTORY_NAME], true)) {
            return;
        }

        $details = $payment->getDetails();

        $details['metadata']['refund']['items'] = $this->unitRefundFilter->filterUnitRefunds($unitsRefunded->units(), OrderItemUnitRefund::class);
        $details['metadata']['refund']['shipments'] = $this->unitRefundFilter->filterUnitRefunds($unitsRefunded->units(), ShipmentRefund::class);
        $payment->setDetails($details);

        $hash = $details['metadata']['refund_token'];

        $token = $this->payum->getTokenStorage()->find($hash);
        if (!$token instanceof TokenInterface) {
            $this->loggerAction->addNegativeLog(sprintf('A token with hash `%s` could not be found.', $hash));

            throw new BadRequestHttpException(sprintf('A token with hash `%s` could not be found.', $hash));
        }

        $gateway = $this->payum->getGateway($token->getGatewayName());

        try {
            if (isset($payment->getDetails()['order_mollie_id'])) {
                $gateway->execute(new RefundOrder($token));
            } else {
                $gateway->execute(new RefundAction($token));
            }
        } catch (UpdateHandlingException $e) {
            $this->loggerAction->addNegativeLog(sprintf('Error with refund: %s', $e->getMessage()));
        }
    }
}
