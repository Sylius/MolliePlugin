<?php


declare(strict_types=1);

namespace Tests\Sylius\MolliePlugin\Behat\Context\Ui\Shop;

use Behat\Behat\Context\Context;
use Sylius\Component\Core\Repository\PaymentRepositoryInterface;
use Sylius\Component\Payment\Model\PaymentInterface;

final class ProductContext implements Context
{
    private PaymentRepositoryInterface $paymentRepository;

    private const SUCCESSFUL_PAYMENT_AMOUNT = 1999;

    public function __construct(PaymentRepositoryInterface $paymentRepository)
    {
        $this->paymentRepository = $paymentRepository;
    }

    /**
     * @When I fix payment amount to pay successfully
     */
    public function iFixPaymentAmountToPaySuccessfully(): void
    {
        /** @var PaymentInterface $payment */
        $payment = $this->paymentRepository->findOneBy([
            'state' => PaymentInterface::STATE_NEW,
        ]);

        $payment->setAmount(self::SUCCESSFUL_PAYMENT_AMOUNT);

        $this->paymentRepository->add($payment);
    }
}
