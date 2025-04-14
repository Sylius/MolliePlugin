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

namespace Sylius\MolliePlugin\Mailer\Sender;

use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Mailer\Sender\SenderInterface;
use Sylius\MolliePlugin\Entity\TemplateMollieEmailTranslationInterface;
use Sylius\MolliePlugin\Mailer\Emails;
use Sylius\MolliePlugin\Twig\Parser\ContentParserInterface;
use Webmozart\Assert\Assert;

final class PaymentLinkEmailSender implements PaymentLinkEmailSenderInterface
{
    public function __construct(
        private readonly SenderInterface $emailSender,
        private readonly ContentParserInterface $contentParser,
    ) {
    }

    public function sendConfirmationEmail(
        OrderInterface $order,
        TemplateMollieEmailTranslationInterface $template,
    ): void {
        /** @var PaymentInterface|null $payment */
        $payment = $order->getPayments()->last();

        if (null === $payment || 0 === count($payment->getDetails())) {
            return;
        }

        $paymentLink = $payment->getDetails()['payment_mollie_link'];

        Assert::notNull($template->getContent());
        $content = $this->contentParser->parse($template->getContent(), $paymentLink);

        /** @var CustomerInterface $customer */
        $customer = $order->getCustomer();

        $this->emailSender->send(Emails::PAYMENT_LINK, [$customer->getEmail()], [
            'order' => $order,
            'template' => $template,
            'content' => $content,
        ]);
    }
}
