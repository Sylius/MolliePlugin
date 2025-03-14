<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\EventListener;

use Sylius\MolliePlugin\Exceptions\InvalidRefundAmountException;
use Sylius\MolliePlugin\Logger\MollieLoggerActionInterface;
use Sylius\MolliePlugin\Order\OrderPaymentRefundInterface;
use Sylius\RefundPlugin\Event\UnitsRefunded;
use Symfony\Component\Messenger\Exception\HandlerFailedException;

final class PaymentPartialEventListener
{
    /** @var OrderPaymentRefundInterface */
    private $orderPaymentRefund;

    /** @var MollieLoggerActionInterface */
    private $loggerAction;

    public function __construct(
        OrderPaymentRefundInterface $orderPaymentRefund,
        MollieLoggerActionInterface $loggerAction
    ) {
        $this->orderPaymentRefund = $orderPaymentRefund;
        $this->loggerAction = $loggerAction;
    }

    public function __invoke(UnitsRefunded $unitRefunded): void
    {
        try {
            $this->orderPaymentRefund->refund($unitRefunded);
        } catch (InvalidRefundAmountException $exception) {
            $this->loggerAction->addNegativeLog($exception->getMessage());
        } catch (HandlerFailedException $exception) {
            /** @var \Exception $previousException */
            $previousException = $exception->getPrevious();

            $this->loggerAction->addNegativeLog($previousException->getMessage());
        }
    }
}
