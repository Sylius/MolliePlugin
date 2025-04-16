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

namespace Sylius\MolliePlugin\Mailer\Manager;

use Liip\ImagineBundle\Exception\Config\Filter\NotFoundException;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\MolliePlugin\Entity\TemplateMollieEmailTranslationInterface;
use Sylius\MolliePlugin\Mailer\Sender\PaymentLinkEmailSenderInterface;
use Sylius\MolliePlugin\Repository\TemplateMollieEmailTranslationRepositoryInterface;

final class PaymentLinkEmailManager implements PaymentLinkEmailManagerInterface
{
    public function __construct(
        private readonly TemplateMollieEmailTranslationRepositoryInterface $templateRepository,
        private readonly PaymentLinkEmailSenderInterface $emailSender,
    ) {
    }

    public function send(OrderInterface $order, string $templateName): void
    {
        $locale = $order->getLocaleCode();

        if (null === $locale) {
            return;
        }
        /** @var ?TemplateMollieEmailTranslationInterface $template */
        $template = $this->templateRepository->findOneByLocaleCodeAdnType(
            $locale,
            $templateName,
        );

        if (null === $template) {
            throw new NotFoundException(\sprintf('Not payment link template found, or not translation added'));
        }

        $this->emailSender->sendConfirmationEmail($order, $template);
    }
}
