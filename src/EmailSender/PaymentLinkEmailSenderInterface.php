<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\EmailSender;

use Sylius\MolliePlugin\Entity\TemplateMollieEmailTranslationInterface;
use Sylius\Component\Core\Model\OrderInterface;

interface PaymentLinkEmailSenderInterface
{
    public function sendConfirmationEmail(OrderInterface $order, TemplateMollieEmailTranslationInterface $template): void;
}
