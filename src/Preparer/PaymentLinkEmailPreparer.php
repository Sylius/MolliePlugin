<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Preparer;

use Sylius\MolliePlugin\EmailSender\PaymentLinkEmailSenderInterface;
use Sylius\MolliePlugin\Entity\TemplateMollieEmailTranslationInterface;
use Sylius\MolliePlugin\Repository\TemplateMollieEmailTranslationRepositoryInterface;
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
