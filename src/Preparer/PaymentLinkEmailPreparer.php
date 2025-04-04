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

namespace SyliusMolliePlugin\Preparer;

use SyliusMolliePlugin\EmailSender\PaymentLinkEmailSenderInterface;
use SyliusMolliePlugin\Entity\TemplateMollieEmailTranslationInterface;
use SyliusMolliePlugin\Repository\TemplateMollieEmailTranslationRepositoryInterface;
use Liip\ImagineBundle\Exception\Config\Filter\NotFoundException;
use Sylius\Component\Core\Model\OrderInterface;

final class PaymentLinkEmailPreparer implements PaymentLinkEmailPreparerInterface
{
    /** @var TemplateMollieEmailTranslationRepositoryInterface */
    private $templateRepository;

    /** @var PaymentLinkEmailSenderInterface */
    private $emailSender;

    public function __construct(TemplateMollieEmailTranslationRepositoryInterface $templateRepository, PaymentLinkEmailSenderInterface $emailSender)
    {
        $this->templateRepository = $templateRepository;
        $this->emailSender = $emailSender;
    }

    public function prepare(OrderInterface $order, string $templateName): void
    {
        $locale = $order->getLocaleCode();

        if (null === $locale) {
            return;
        }
        /** @var ?TemplateMollieEmailTranslationInterface $template */
        $template = $this->templateRepository->findOneByLocaleCodeAdnType(
            $locale,
            $templateName
        );

        if (null === $template) {
            throw new NotFoundException(\sprintf('Not payment link template found, or not translation added'));
        }

        $this->emailSender->sendConfirmationEmail($order, $template);
    }
}
