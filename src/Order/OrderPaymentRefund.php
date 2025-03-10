<?php


declare(strict_types=1);

namespace SyliusMolliePlugin\Order;

use SyliusMolliePlugin\Factory\MollieGatewayFactory;
use SyliusMolliePlugin\Factory\MollieSubscriptionGatewayFactory;
use SyliusMolliePlugin\Logger\MollieLoggerActionInterface;
use SyliusMolliePlugin\Request\Api\RefundOrder;
use Payum\Core\Payum;
use Payum\Core\Request\Refund as RefundAction;
use Payum\Core\Security\TokenInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Resource\Exception\UpdateHandlingException;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\RefundPlugin\Event\UnitsRefunded;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Webmozart\Assert\Assert;

final class OrderPaymentRefund implements OrderPaymentRefundInterface
{
    /** @var RepositoryInterface */
    private $orderRepository;

    /** @var MollieLoggerActionInterface */
    private $loggerAction;

    /** @var Payum */
    private $payum;

    public function __construct(
        RepositoryInterface $orderRepository,
        MollieLoggerActionInterface $loggerAction,
        Payum $payum
    ) {
        $this->orderRepository = $orderRepository;
        $this->loggerAction = $loggerAction;
        $this->payum = $payum;
    }

    public function refund(UnitsRefunded $units): void
    {
        /** @var OrderInterface $order */
        $order = $this->orderRepository->findOneBy(['number' => $units->orderNumber()]);

        /** @var PaymentInterface|null $payment */
        $payment = $order->getPayments()->last();
        if (null === $payment) {
            $this->loggerAction->addNegativeLog(sprintf('Not fount payment in refund'));

            throw new NotFoundHttpException();
        }

        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $payment->getMethod();

        Assert::notNull($paymentMethod->getGatewayConfig());
        $factoryName = $paymentMethod->getGatewayConfig()->getFactoryName() ?? null;

        if (false === in_array($factoryName, [MollieGatewayFactory::FACTORY_NAME, MollieSubscriptionGatewayFactory::FACTORY_NAME], true)) {
            return;
        }

        $details = $payment->getDetails();

        $details['metadata']['refund']['items'] = $units->units();
        $details['metadata']['refund']['shipments'] = $units->shipments();
        $payment->setDetails($details);

        $hash = $details['metadata']['refund_token'];

        /** @var TokenInterface|mixed $token */
        $token = $this->payum->getTokenStorage()->find($hash);

        if (null === $token || !$token instanceof TokenInterface) {
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
