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

namespace SyliusMolliePlugin\Action;

use Mollie\Api\Types\PaymentMethod;
use SyliusMolliePlugin\Action\Api\BaseApiAwareAction;
use SyliusMolliePlugin\Entity\MollieGatewayConfigInterface;
use SyliusMolliePlugin\Factory\ApiCustomerFactoryInterface;
use SyliusMolliePlugin\Helper\ConvertOrderInterface;
use SyliusMolliePlugin\Helper\IntToStringConverterInterface;
use SyliusMolliePlugin\Helper\PaymentDescriptionInterface;
use SyliusMolliePlugin\Payments\PaymentTerms\Options;
use SyliusMolliePlugin\Provider\Divisor\DivisorProviderInterface;
use SyliusMolliePlugin\Resolver\PaymentLocaleResolverInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Convert;
use Payum\Core\Request\GetCurrency;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Customer\Context\CustomerContextInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Webmozart\Assert\Assert;

final class ConvertMolliePaymentAction extends BaseApiAwareAction implements ActionInterface, GatewayAwareInterface, ApiAwareInterface
{
    use GatewayAwareTrait;

    /** @var PaymentDescriptionInterface */
    private $paymentDescription;

    /** @var RepositoryInterface */
    private $mollieMethodsRepository;

    /** @var ConvertOrderInterface */
    private $orderConverter;

    /** @var CustomerContextInterface */
    private $customerContext;

    /** @var PaymentLocaleResolverInterface */
    private $paymentLocaleResolver;

    /** @var ApiCustomerFactoryInterface */
    private $apiCustomerFactory;

    /** @var IntToStringConverterInterface */
    private $intToStringConverter;

    /** @var DivisorProviderInterface */
    private $divisorProvider;

    public function __construct(
        PaymentDescriptionInterface $paymentDescription,
        RepositoryInterface $mollieMethodsRepository,
        ConvertOrderInterface $orderConverter,
        CustomerContextInterface $customerContext,
        PaymentLocaleResolverInterface $paymentLocaleResolver,
        ApiCustomerFactoryInterface $apiCustomerFactory,
        IntToStringConverterInterface $intToStringConverter,
        DivisorProviderInterface $divisorProvider
    ) {
        $this->paymentDescription = $paymentDescription;
        $this->mollieMethodsRepository = $mollieMethodsRepository;
        $this->orderConverter = $orderConverter;
        $this->customerContext = $customerContext;
        $this->paymentLocaleResolver = $paymentLocaleResolver;
        $this->apiCustomerFactory = $apiCustomerFactory;
        $this->intToStringConverter = $intToStringConverter;
        $this->divisorProvider = $divisorProvider;
    }

    /** @param Convert|mixed $request */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getSource();

        /** @var OrderInterface $order */
        $order = $payment->getOrder();

        /** @var CustomerInterface $customer */
        $customer = $order->getCustomer();

        Assert::notNull($payment->getCurrencyCode());
        $this->gateway->execute($currency = new GetCurrency($payment->getCurrencyCode()));

        $divisor = $this->divisorProvider->getDivisorForCurrency($currency);

        Assert::notNull($payment->getAmount());
        $amount = $this->intToStringConverter->convertIntToString($payment->getAmount(), $divisor);

        $paymentOptions = $payment->getDetails();

        if (isset($paymentOptions['metadata'])) {
            $paymentMethod = $paymentOptions['metadata']['molliePaymentMethods'] ?? null;
            $cartToken = $paymentOptions['metadata']['cartToken'];
            $saveCardInfo = $paymentOptions['metadata']['saveCardInfo'];
            $useSavedCards = $paymentOptions['metadata']['useSavedCards'];
        } else {
            $paymentMethod = $paymentOptions['molliePaymentMethods'] ?? null;
            $cartToken = $paymentOptions['cartToken'] ?? null;
            $saveCardInfo = $paymentOptions['saveCardInfo'] ?? null;
            $useSavedCards = $paymentOptions['useSavedCards'] ?? null;
        }

        /** @var MollieGatewayConfigInterface $method */
        $method = $this->mollieMethodsRepository->findOneBy(['methodId' => $paymentMethod]);
        $gatewayConfig = $method->getGateway()->getConfig();
        $details = [
            'amount' => [
                'value' => "$amount",
                'currency' => $currency->code,
            ],
            'description' => $this->paymentDescription->getPaymentDescription($payment, $method, $order),
            'metadata' => [
                'order_id' => $order->getId(),
                'customer_id' => $customer->getId() ?? null,
                'molliePaymentMethods' => $paymentMethod ?? null,
                'cartToken' => $cartToken ?? null,
                'saveCardInfo' => $saveCardInfo ?? null,
                'useSavedCards' => $useSavedCards ?? null,
            ],
            'full_name' => $customer->getFullName() ?? null,
            'email' => $customer->getEmail() ?? null,
        ];

        if (null !== $this->customerContext->getCustomer() && true === ($gatewayConfig['single_click_enabled'] ?? false)) {
            $mollieCustomer = $this->apiCustomerFactory->createNew($details);
            $this->gateway->execute($mollieCustomer);
            $model = $mollieCustomer->getModel();
            $details['metadata']['customer_mollie_id'] = $model['customer_mollie_id'];
        }

        if ($method->getMethodId() === PaymentMethod::ALMA || $method->getMethodId() === PaymentMethod::TRUSTLY) {
            $billingAddress = $order->getBillingAddress();
            $customer = $order->getCustomer();
            $address = [];

            if (null !== $billingAddress) {
                $email = $customer?->getEmail() ?? $order->getUser()?->getEmail() ?? null;

                $address = [
                    'streetAndNumber' => $billingAddress->getStreet(),
                    'postalCode' => $billingAddress->getPostcode(),
                    'city' => $billingAddress->getCity(),
                    'country' => $billingAddress->getCountryCode(),
                    'givenName' => $billingAddress->getFirstName(),
                    'familyName' => $billingAddress->getLastName(),
                    'organizationName' => $billingAddress->getCompany(),
                    'email' => $email
                ];
            }

            $details['billingAddress'] = $address;
        }

        if (false === $this->mollieApiClient->isRecurringSubscription()) {
            $details['customerId'] = $model['customer_mollie_id'] ?? null;
            $details['metadata']['methodType'] = Options::PAYMENT_API;

            if (null !== ($paymentLocale = $this->paymentLocaleResolver->resolveFromOrder($order))) {
                $details['locale'] = $paymentLocale;
            }

            if (Options::ORDER_API === array_search($method->getPaymentType(), Options::getAvailablePaymentType(), true)) {
                unset($details['customerId']);

                $details['metadata']['methodType'] = Options::ORDER_API;
                $details = $this->orderConverter->convert($order, $details, $divisor, $method);
            }
        }

        $request->setResult($details);
    }

    public function supports($request): bool
    {
        return
            $request instanceof Convert &&
            $request->getSource() instanceof PaymentInterface &&
            'array' === $request->getTo();
    }
}
