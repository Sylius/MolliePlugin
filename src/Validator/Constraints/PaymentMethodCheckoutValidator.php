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

use Mollie\Api\Types\PaymentMethod;
use Payum\Core\Model\GatewayConfigInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\MolliePlugin\Payum\Checker\MollieGatewayFactoryCheckerInterface;
use Sylius\MolliePlugin\Resolver\Order\PaymentCheckoutOrderResolverInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class PaymentMethodCheckoutValidator extends ConstraintValidator
{
    public function __construct(
        private readonly PaymentCheckoutOrderResolverInterface $paymentCheckoutOrderResolver,
        private readonly RequestStack $requestStack,
        private readonly MollieGatewayFactoryCheckerInterface $mollieGatewayFactoryChecker,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        $order = $this->paymentCheckoutOrderResolver->resolve();

        /** @var PaymentInterface|false|null $payment */
        $payment = $order->getPayments()->last();

        if (!$payment instanceof PaymentInterface) {
            return;
        }

        /** @var ?PaymentMethodInterface $paymentMethod */
        $paymentMethod = $payment->getMethod();
        if (null === $paymentMethod) {
            return;
        }

        /** @var GatewayConfigInterface $gateway */
        $gateway = $paymentMethod->getGatewayConfig();

        if ($value === null && $this->mollieGatewayFactoryChecker->isMollieGateway($gateway)) {
            $this->flashMessage($constraint, 'error', 'sylius_mollie.empty_payment_method_checkout');
        }

        if ($value === PaymentMethod::BILLIE && empty($order->getBillingAddress()->getCompany())) {
            $this->flashMessage($constraint, 'error', 'sylius_mollie.billie.error.company_missing');
        }

        $customer = $order->getCustomer();
        $customerEmail = $customer?->getEmail();
        $orderEmail = $order->getUser() !== null ? $order->getUser()->getEmail() : null;

        $email = $customerEmail ?? $orderEmail;

        if (($value === PaymentMethod::TRUSTLY || $value === PaymentMethod::ALMA) &&
            (
                empty($order->getBillingAddress()->getFirstName()) ||
                empty($order->getBillingAddress()->getLastName()) ||
                empty($email)
            )) {
            $this->flashMessage($constraint, 'error', 'sylius_mollie.billing_address.error.info_missing');
        }
    }

    private function flashMessage(Constraint $constraint, string $type, string $messageKey): void
    {
        /** @var Session $session */
        $session = $this->requestStack->getSession();
        $session->getFlashBag()->add($type, $messageKey);
        if (!property_exists($constraint, 'message')) {
            throw new \InvalidArgumentException();
        }

        $this->context->buildViolation($constraint->message)->setTranslationDomain('messages')->addViolation();
    }
}
