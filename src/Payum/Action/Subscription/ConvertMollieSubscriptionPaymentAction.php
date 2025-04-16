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

namespace Sylius\MolliePlugin\Payum\Action\Subscription;

use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Convert;
use Payum\Core\Request\GetCurrency;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Customer\Context\CustomerContextInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\MolliePlugin\Converter\IntToStringConverterInterface;
use Sylius\MolliePlugin\Converter\OrderConverterInterface;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\MolliePlugin\Payum\Action\BaseApiAwareAction;
use Sylius\MolliePlugin\Payum\Request\CreateCustomer;
use Sylius\MolliePlugin\Provider\DivisorProviderInterface;
use Sylius\MolliePlugin\Provider\PaymentDescriptionProviderInterface;
use Sylius\MolliePlugin\Resolver\PaymentLocaleResolverInterface;
use Webmozart\Assert\Assert;

final class ConvertMollieSubscriptionPaymentAction extends BaseApiAwareAction implements GatewayAwareInterface
{
    use GatewayAwareTrait;

    public function __construct(
        private PaymentDescriptionProviderInterface $paymentDescriptionProvider,
        private RepositoryInterface $mollieMethodsRepository,
        private OrderConverterInterface $orderConverter,
        private CustomerContextInterface $customerContext,
        private PaymentLocaleResolverInterface $paymentLocaleResolver,
        private IntToStringConverterInterface $intToStringConverter,
        private DivisorProviderInterface $divisorProvider,
    ) {
    }

    /** @param Convert|mixed $request */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getSource();

        /** @var OrderInterface $order */
        $order = $payment->getOrder();
        if (null === $order->getRecurringSequenceIndex()) {
            $order->setRecurringSequenceIndex(0);
        }

        /** @var CustomerInterface $customer */
        $customer = $order->getCustomer();

        Assert::notNull($payment->getCurrencyCode());
        $this->gateway->execute($currency = new GetCurrency($payment->getCurrencyCode()));

        $divisor = $this->divisorProvider->getDivisorForCurrency($currency);

        Assert::notNull($payment->getAmount());
        $amount = $this->intToStringConverter->convertIntToString($payment->getAmount(), $divisor);
        $paymentOptions = $payment->getDetails();

        $cartToken = $paymentOptions['cartToken'];
        $sequenceType = array_key_exists(
            'recurring',
            $paymentOptions,
        ) && true === $paymentOptions['recurring'] ? 'recurring' : 'first';

        if (isset($paymentOptions['metadata'])) {
            $paymentMethod = $paymentOptions['metadata']['molliePaymentMethods'] ?? null;
        } else {
            $paymentMethod = $paymentOptions['molliePaymentMethods'] ?? null;
        }

        $details = [
            'amount' => [
                'value' => "$amount",
                'currency' => $currency->code,
            ],
            'description' => $order->getNumber(),
            'sequenceType' => $sequenceType,
            'metadata' => [
                'order_id' => $order->getId(),
                'customer_id' => $customer->getId() ?? null,
                'molliePaymentMethods' => $paymentMethod ?? null,
                'cartToken' => $cartToken ?? null,
                'sequenceType' => $sequenceType,
                'gateway' => $request->getToken()->getGatewayName(),
            ],
            'full_name' => $customer->getFullName(),
            'email' => $customer->getEmail() ?? null,
        ];
        $details['metadata'] = array_merge($details['metadata'], $paymentOptions['metadata'] ?? []);

        if (isset($paymentOptions['mandateId'])) {
            $details['mandateId'] = $paymentOptions['mandateId'];
        }

        $this->gateway->execute($mollieCustomer = new CreateCustomer($details));
        $model = $mollieCustomer->getModel();
        $details['customerId'] = $model['customer_mollie_id'];

        $request->setResult($details);
    }

    public function supports($request): bool
    {
        return
            $request instanceof Convert &&
            $request->getSource() instanceof PaymentInterface &&
            $request->getSource()->getOrder() instanceof OrderInterface &&
            'array' === $request->getTo();
    }
}
