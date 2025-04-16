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

namespace Sylius\MolliePlugin\Validator\Constraints;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\MolliePlugin\Payum\Factory\MollieGatewayFactory;
use Sylius\MolliePlugin\Payum\Factory\MollieSubscriptionGatewayFactory;
use Sylius\MolliePlugin\Repository\PaymentMethodRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator as ConstraintValidatorAlias;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmozart\Assert\Assert;

final class PaymentMethodMollieChannelUniqueValidator extends ConstraintValidatorAlias
{
    public function __construct(
        private readonly PaymentMethodRepositoryInterface $paymentMethodRepository,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if ($value instanceof PaymentMethodInterface &&
            null !== $value->getCode() &&
            $this->isMolliePaymentMethod($value)
        ) {
            $this->validateMolliePaymentMethod($value, $constraint);
        }
    }

    private function validateMolliePaymentMethod(PaymentMethodInterface $paymentMethod, Constraint $constraint): void
    {
        if (null === $paymentMethod->getCode()) {
            return;
        }
        $molliePaymentMethods = $this->paymentMethodRepository->findAllByFactoryNameAndCode($paymentMethod->getCode());

        if (0 === count($molliePaymentMethods)) {
            return;
        }

        $separatedMethods = [
            MollieSubscriptionGatewayFactory::FACTORY_NAME => [],
            MollieGatewayFactory::FACTORY_NAME => [],
        ];

        foreach ($molliePaymentMethods as $method) {
            Assert::notNull($method->getGatewayConfig());
            $separatedMethods[$method->getGatewayConfig()->getFactoryName()][] = $method;
        }

        foreach ($separatedMethods as $gatewayName => $methodsCollection) {
            $alreadyUsedChannels = $this->getAlreadyUsedChannels($methodsCollection);

            Assert::notNull($paymentMethod->getGatewayConfig());
            if ($paymentMethod->getGatewayConfig()->getFactoryName() !== $gatewayName) {
                continue;
            }

            if ($this->isTheSameChannel($paymentMethod->getChannels(), $alreadyUsedChannels)) {
                $translation = $this->translator->trans('sylius_mollie.form.channel_should_be_unique', [
                    '{channels}' => $this->getChannelsNameByChannels($alreadyUsedChannels),
                ]);

                $this->context->buildViolation($translation)->atPath('channels')->addViolation();
            }
        }
    }

    /** @param PaymentMethodInterface[] $molliePaymentMethods */
    private function getAlreadyUsedChannels(array $molliePaymentMethods): Collection
    {
        $alreadyUsedChannels = new ArrayCollection();

        foreach ($molliePaymentMethods as $molliePaymentMethod) {
            /** @var ChannelInterface $channel */
            foreach ($molliePaymentMethod->getChannels() as $channel) {
                if (!$alreadyUsedChannels->contains($channel)) {
                    $alreadyUsedChannels->add($channel);
                }
            }
        }

        return $alreadyUsedChannels;
    }

    private function isTheSameChannel(Collection $newChannels, Collection $paymentMethodExistingChannels): bool
    {
        foreach ($paymentMethodExistingChannels as $paymentMethodExistingChannel) {
            if ($newChannels->contains($paymentMethodExistingChannel)) {
                return true;
            }
        }

        return false;
    }

    private function isMolliePaymentMethod(PaymentMethodInterface $paymentMethod): bool
    {
        /** @var GatewayConfigInterface|null $gateway */
        $gateway = $paymentMethod->getGatewayConfig();
        if (null === $gateway) {
            return false;
        }

        return true === in_array(
            $gateway->getFactoryName(),
            [MollieGatewayFactory::FACTORY_NAME, MollieSubscriptionGatewayFactory::FACTORY_NAME],
            true,
        );
    }

    private function getChannelsNameByChannels(Collection $alreadyUsedChannels): string
    {
        $channelsNames = '';

        /** @var ChannelInterface $channel */
        foreach ($alreadyUsedChannels as $channel) {
            $channelsNames .= \sprintf('%s ', $channel->getName());
        }

        return $channelsNames;
    }
}
