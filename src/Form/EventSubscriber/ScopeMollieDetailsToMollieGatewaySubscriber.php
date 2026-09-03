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

namespace Sylius\MolliePlugin\Form\EventSubscriber;

use Payum\Core\Model\GatewayConfigInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Sylius\Component\Payment\Model\PaymentMethodInterface;
use Sylius\MolliePlugin\Form\Type\PaymentMollieType;
use Sylius\MolliePlugin\Payum\Checker\MollieGatewayFactoryCheckerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\Valid;

final class ScopeMollieDetailsToMollieGatewaySubscriber implements EventSubscriberInterface
{
    private const DETAILS_FIELD = 'details';

    private const METHOD_FIELD = 'method';

    private const MOLLIE_KEYS = [
        PaymentMollieType::FIELD_PAYMENT_METHODS,
        PaymentMollieType::FIELD_CART_TOKEN,
        PaymentMollieType::FIELD_SAVE_CARD_INFO,
        PaymentMollieType::FIELD_USE_SAVED_CARDS,
    ];

    private bool $storedMethodWasMollie = false;

    private bool $detailsFieldRemoved = false;

    public function __construct(
        private readonly MollieGatewayFactoryCheckerInterface $mollieGatewayFactoryChecker,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA => 'removeDetailsWhenNoMollieMethodIsAvailable',
            // last, so a `details` field another integration adds on submit is already in place
            FormEvents::PRE_SUBMIT => ['unmapDetailsForUnrelatedGateways', -10],
        ];
    }

    public function removeDetailsWhenNoMollieMethodIsAvailable(FormEvent $event): void
    {
        $form = $event->getForm();
        $payment = $event->getData();

        $this->storedMethodWasMollie = $payment instanceof PaymentInterface &&
            $this->isMollieMethod($payment->getMethod());

        if ($this->ownsDetails($form) && $this->noMollieMethodAvailable($form)) {
            $form->remove(self::DETAILS_FIELD);

            $this->detailsFieldRemoved = true;
        }
    }

    public function unmapDetailsForUnrelatedGateways(FormEvent $event): void
    {
        $form = $event->getForm();
        $data = $event->getData();

        if (!$form->has(self::DETAILS_FIELD)) {
            $this->discardSubmittedDetails($event);

            return;
        }

        if (!$this->ownsDetails($form)) {
            return;
        }

        if ($this->isMollieMethod($this->resolveSubmittedMethod($form, $data))) {
            return;
        }

        $this->addDetailsField($form, false);
        $this->discardKeysWrittenByMollie($form);
    }

    public function addDetailsField(FormBuilderInterface|FormInterface $form, bool $mapped = true): void
    {
        $form->add(self::DETAILS_FIELD, PaymentMollieType::class, [
            'mapped' => $mapped,
            'validation_groups' => ['sylius'],
            'constraints' => [
                new Valid(),
            ],
            PaymentMollieType::OPTION_MOLLIE_METHODS => $this->alreadyResolvedMollieMethods($form),
        ]);
    }

    /** @return array<string, mixed>|null */
    private function alreadyResolvedMollieMethods(FormBuilderInterface|FormInterface $form): ?array
    {
        if (!$form instanceof FormInterface || !$this->ownsDetails($form)) {
            return null;
        }

        $methods = $form->get(self::DETAILS_FIELD)->getConfig()->getAttribute(PaymentMollieType::OPTION_MOLLIE_METHODS);

        return is_array($methods) ? $methods : null;
    }

    /** Only what this subscriber removed is dropped; a `details` field owned by someone else is not ours to clear. */
    private function discardSubmittedDetails(FormEvent $event): void
    {
        $data = $event->getData();

        if (!$this->detailsFieldRemoved || !is_array($data) || !array_key_exists(self::DETAILS_FIELD, $data)) {
            return;
        }

        unset($data[self::DETAILS_FIELD]);

        $event->setData($data);
    }

    /** Sylius reuses the payment until the previous attempt is cancelled or failed, so Mollie's keys outlive a switch away from it. */
    private function discardKeysWrittenByMollie(FormInterface $form): void
    {
        $payment = $form->getData();

        if (!$this->storedMethodWasMollie || !$payment instanceof PaymentInterface) {
            return;
        }

        $details = $payment->getDetails();
        $remaining = array_diff_key($details, array_flip(self::MOLLIE_KEYS));

        if ($remaining !== $details) {
            $payment->setDetails($remaining);
        }
    }

    private function ownsDetails(FormInterface $form): bool
    {
        if (!$form->has(self::DETAILS_FIELD)) {
            return false;
        }

        return $form->get(self::DETAILS_FIELD)->getConfig()->getType()->getInnerType() instanceof PaymentMollieType;
    }

    private function noMollieMethodAvailable(FormInterface $form): bool
    {
        $choiceList = $this->methodChoiceList($form);

        if (null === $choiceList) {
            return false;
        }

        foreach ($choiceList->getChoices() as $choice) {
            if ($this->isMollieMethod($choice)) {
                return false;
            }
        }

        return true;
    }

    private function resolveSubmittedMethod(FormInterface $form, mixed $data): ?PaymentMethodInterface
    {
        if (!is_array($data)) {
            return null;
        }

        $submittedMethod = $data[self::METHOD_FIELD] ?? null;

        if (!is_string($submittedMethod) && !is_int($submittedMethod)) {
            return null;
        }

        $choiceList = $this->methodChoiceList($form);

        if (null === $choiceList) {
            return null;
        }

        $choices = $choiceList->getChoicesForValues([(string) $submittedMethod]);
        $method = $choices[0] ?? null;

        return $method instanceof PaymentMethodInterface ? $method : null;
    }

    private function methodChoiceList(FormInterface $form): ?ChoiceListInterface
    {
        if (!$form->has(self::METHOD_FIELD)) {
            return null;
        }

        $methodConfig = $form->get(self::METHOD_FIELD)->getConfig();

        if (!$methodConfig->hasAttribute('choice_list')) {
            return null;
        }

        $choiceList = $methodConfig->getAttribute('choice_list');

        return $choiceList instanceof ChoiceListInterface ? $choiceList : null;
    }

    private function isMollieMethod(mixed $paymentMethod): bool
    {
        if (!$paymentMethod instanceof PaymentMethodInterface) {
            return false;
        }

        $gatewayConfig = $paymentMethod->getGatewayConfig();

        if (!$gatewayConfig instanceof GatewayConfigInterface) {
            return false;
        }

        return $this->mollieGatewayFactoryChecker->isMollieGateway($gatewayConfig);
    }
}
