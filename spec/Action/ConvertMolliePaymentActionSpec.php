<?php


declare(strict_types=1);

namespace spec\Sylius\MolliePlugin\Action;

use ArrayObject;
use Sylius\MolliePlugin\Action\ConvertMolliePaymentAction;
use Sylius\MolliePlugin\Client\MollieApiClient;
use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\MolliePlugin\Entity\MollieGatewayConfigInterface;
use Sylius\MolliePlugin\Factory\ApiCustomerFactoryInterface;
use Sylius\MolliePlugin\Helper\ConvertOrderInterface;
use Sylius\MolliePlugin\Helper\IntToStringConverterInterface;
use Sylius\MolliePlugin\Request\Api\CreateCustomer;
use Sylius\MolliePlugin\Resolver\PaymentLocaleResolverInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Convert;
use Payum\Core\Request\GetCurrency;
use PhpSpec\ObjectBehavior;
use Sylius\MolliePlugin\Helper\PaymentDescriptionInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Customer\Context\CustomerContextInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class ConvertMolliePaymentActionSpec extends ObjectBehavior
{
    function let(
        PaymentDescriptionInterface $paymentDescriptionProvider,
        SessionInterface $session,
        RepositoryInterface $mollieMethodsRepository,
        ConvertOrderInterface $orderConverter,
        CustomerContextInterface $customerContext,
        PaymentLocaleResolverInterface $paymentLocaleResolver,
        ApiCustomerFactoryInterface $apiCustomerFactory
    ): void {
        $this->beConstructedWith(
            $paymentDescriptionProvider,
            $session,
            $mollieMethodsRepository,
            $orderConverter,
            $customerContext,
            $paymentLocaleResolver,
            $apiCustomerFactory
        );
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(ConvertMolliePaymentAction::class);
    }

    function it_implements_action_interface(): void
    {
        $this->shouldHaveType(ActionInterface::class);
    }

    function it_executes_when_metadata_and_single_click_are_enabled(
        Convert $request,
        PaymentInterface $payment,
        OrderInterface $order,
        CustomerInterface $customer,
        GatewayInterface $gateway,
        MollieApiClient $mollieApiClient,
        PaymentDescriptionInterface $paymentDescriptionProvider,
        MollieGatewayConfigInterface $method,
        GatewayConfigInterface $gatewayConfig,
        RepositoryInterface $mollieMethodsRepository,
        CustomerContextInterface $customerContext,
        ApiCustomerFactoryInterface $apiCustomerFactory,
        CreateCustomer $mollieCustomer
    ): void {
        $mollieApiClient->isRecurringSubscription()->willReturn(false);
        $this->setApi($mollieApiClient);
        $this->setGateway($gateway);

        $currency = new GetCurrency('EUR');
        $gateway->execute($currency)->shouldBeCalled();

        $customerContext->getCustomer()->willReturn($customer);

        $customer->getFullName()->willReturn('Jan Kowalski');
        $customer->getEmail()->willReturn('shop@example.com');
        $customer->getId()->willReturn(1);

        $order->getId()->willReturn(1);
        $order->getLocaleCode()->willReturn('pl_PL');
        $order->getCustomer()->willReturn($customer);

        $payment->getOrder()->willReturn($order);
        $payment->getAmount()->willReturn(445535);
        $payment->getCurrencyCode()->willReturn('EUR');

        $paymentDescriptionProvider->getPaymentDescription($payment, $method, $order)->willReturn('description');
        $request->getSource()->willReturn($payment);
        $request->getTo()->willReturn('array');

        $payment->getDetails()->willReturn([
            'metadata' => [
                'molliePaymentMethods' => 'ideal',
                'cartToken' => 'carttoken',
                'selected_issuer' => 'issuer',
                'customer_mollie_id' => 15
            ],
        ]);

        $request->getSource()->willReturn($payment);

        $mollieMethodsRepository->findOneBy(['methodId' => 'ideal'])->willReturn($method);
        $method->getPaymentType()->willReturn('payment_type');
        $method->getGateway()->willReturn($gatewayConfig);
        $gatewayConfig->getConfig()->willReturn([
            'single_click_enabled' => true,
        ]);
        $details = [
            'amount' => [
                'value' => '445535.00',
                'currency' => 'EUR',
            ],
            'description' => 'description',
            'metadata' => [
                'order_id' => 1,
                'customer_id' => 1,
                'molliePaymentMethods' => 'ideal',
                'cartToken' => 'carttoken',
                'selected_issuer' => 'issuer'
            ],
            'full_name' => 'Jan Kowalski',
            'email' => 'shop@example.com'
        ];
        $apiCustomerFactory->createNew($details)->willReturn($mollieCustomer);

        $gateway->execute($mollieCustomer)->shouldBeCalled();
        $mollieCustomer->getModel()->willReturn(new ArrayObject(
            [
                'customer_mollie_id' => 15
            ]
        ));

        $request->setResult([
            'amount' => [
                'value' => '445535.00',
                'currency' => 'EUR',
            ],
            'description' => 'description',
            'metadata' => [
                'order_id' => 1,
                'customer_id' => 1,
                'molliePaymentMethods' => 'ideal',
                'cartToken' => 'carttoken',
                'selected_issuer' => 'issuer',
                'customer_mollie_id' => 15,
                'methodType' => 'Payments API',
            ],
            'full_name' => 'Jan Kowalski',
            'email' => 'shop@example.com',
            'customerId' => 15,
        ])->shouldBeCalled();

        $this->execute($request);
    }

    function it_executes_with_no_metadata_and_recurring_subscription_set_to_false(
        Convert $request,
        PaymentInterface $payment,
        OrderInterface $order,
        CustomerInterface $customer,
        GatewayInterface $gateway,
        MollieApiClient $mollieApiClient,
        PaymentDescriptionInterface $paymentDescriptionProvider,
        MollieGatewayConfigInterface $method,
        GatewayConfigInterface $gatewayConfig,
        RepositoryInterface $mollieMethodsRepository
    ): void {
        $mollieApiClient->isRecurringSubscription()->willReturn(false);
        $this->setApi($mollieApiClient);
        $this->setGateway($gateway);
        $customer->getFullName()->willReturn('Jan Kowalski');
        $customer->getEmail()->willReturn('shop@example.com');
        $customer->getId()->willReturn(1);
        $order->getId()->willReturn(1);
        $order->getLocaleCode()->willReturn('pl_PL');
        $order->getCustomer()->willReturn($customer);
        $payment->getOrder()->willReturn($order);
        $payment->getAmount()->willReturn(445535);
        $payment->getCurrencyCode()->willReturn('EUR');

        $paymentDescriptionProvider->getPaymentDescription($payment, $method, $order)->willReturn('description');
        $request->getSource()->willReturn($payment);
        $request->getTo()->willReturn('array');

        $payment->getDetails()->willReturn([
            'molliePaymentMethods' => 15,
            'cartToken' => 'token'
        ]);

        $request->getSource()->willReturn($payment);

        $mollieMethodsRepository->findOneBy(['methodId' => 15])->willReturn($method);
        $method->getPaymentType()->willReturn('payment_type');
        $method->getGateway()->willReturn($gatewayConfig);
        $gatewayConfig->getConfig()->willReturn([]);
        $request->setResult([
            'amount' => [
                'value' => '445535.00',
                'currency' => 'EUR',
            ],
            'description' => 'description',
            'metadata' => [
                'order_id' => 1,
                'customer_id' => 1,
                'molliePaymentMethods' => 15,
                'cartToken' => 'token',
                'selected_issuer' => null,
                'methodType' => 'Payments API',
            ],
            'full_name' => 'Jan Kowalski',
            'email' => 'shop@example.com',
            'customerId' => null
        ])->shouldBeCalled();

        $this->execute($request);
    }

    function it_executes_with_no_metadata_and_recurring_subscription_set_to_false_but_with_payment_locale(
        Convert $request,
        PaymentInterface $payment,
        OrderInterface $order,
        CustomerInterface $customer,
        GatewayInterface $gateway,
        MollieApiClient $mollieApiClient,
        PaymentDescriptionInterface $paymentDescriptionProvider,
        MollieGatewayConfigInterface $method,
        GatewayConfigInterface $gatewayConfig,
        RepositoryInterface $mollieMethodsRepository,
        PaymentLocaleResolverInterface $paymentLocaleResolver
    ): void {
        $mollieApiClient->isRecurringSubscription()->willReturn(false);
        $this->setApi($mollieApiClient);
        $this->setGateway($gateway);
        $customer->getFullName()->willReturn('Jan Kowalski');
        $customer->getEmail()->willReturn('shop@example.com');
        $customer->getId()->willReturn(1);
        $order->getId()->willReturn(1);
        $order->getLocaleCode()->willReturn('pl_PL');
        $order->getCustomer()->willReturn($customer);
        $payment->getOrder()->willReturn($order);
        $payment->getAmount()->willReturn(445535);
        $payment->getCurrencyCode()->willReturn('EUR');

        $paymentDescriptionProvider->getPaymentDescription($payment, $method, $order)->willReturn('description');
        $request->getSource()->willReturn($payment);
        $request->getTo()->willReturn('array');

        $payment->getDetails()->willReturn([
            'molliePaymentMethods' => 15,
            'cartToken' => 'token'
        ]);

        $request->getSource()->willReturn($payment);

        $mollieMethodsRepository->findOneBy(['methodId' => 15])->willReturn($method);
        $method->getPaymentType()->willReturn('payment_type');
        $method->getGateway()->willReturn($gatewayConfig);
        $gatewayConfig->getConfig()->willReturn([]);
        $paymentLocaleResolver->resolveFromOrder($order)->willReturn('payment_locale');

        $request->setResult([
            'amount' => [
                'value' => '445535.00',
                'currency' => 'EUR',
            ],
            'description' => 'description',
            'metadata' => [
                'order_id' => 1,
                'customer_id' => 1,
                'molliePaymentMethods' => 15,
                'cartToken' => 'token',
                'selected_issuer' => null,
                'methodType' => 'Payments API',
            ],
            'full_name' => 'Jan Kowalski',
            'email' => 'shop@example.com',
            'customerId' => null,
            'locale' => 'payment_locale'
        ])->shouldBeCalled();

        $this->execute($request);
    }

    function it_executes_with_no_metadata_and_recurring_subscription_set_to_false_but_with_payment_locale_and_order_api(
        Convert $request,
        PaymentInterface $payment,
        OrderInterface $order,
        CustomerInterface $customer,
        GatewayInterface $gateway,
        MollieApiClient $mollieApiClient,
        PaymentDescriptionInterface $paymentDescriptionProvider,
        MollieGatewayConfigInterface $method,
        GatewayConfigInterface $gatewayConfig,
        RepositoryInterface $mollieMethodsRepository,
        PaymentLocaleResolverInterface $paymentLocaleResolver,
        ConvertOrderInterface $orderConverter,
        IntToStringConverterInterface $intToStringConverter
    ): void {
        $mollieApiClient->isRecurringSubscription()->willReturn(false);
        $this->setApi($mollieApiClient);
        $this->setGateway($gateway);

        $mollieMethodsRepository->findOneBy(['methodId' => 15])->willReturn($method);
        $method->getPaymentType()->willReturn('ORDER_API');
        $method->getGateway()->willReturn($gatewayConfig);
        $gatewayConfig->getConfig()->willReturn([]);

        $currency = new GetCurrency('EUR');
        $gateway->execute($currency)->shouldBeCalled();
        $divisor = 1;

        $customer->getFullName()->willReturn('Jan Kowalski');
        $customer->getEmail()->willReturn('shop@example.com');
        $customer->getId()->willReturn(1);

        $payment->getOrder()->willReturn($order);
        $payment->getAmount()->willReturn(445535);
        $payment->getCurrencyCode()->willReturn('EUR');
        $payment->getDetails()->willReturn([
            'molliePaymentMethods' => 15,
            'cartToken' => 'token'
        ]);

        $order->getId()->willReturn(1);
        $order->getLocaleCode()->willReturn('pl_PL');
        $order->getCustomer()->willReturn($customer);
        $order->getTotal()->willReturn(445535);

        $paymentDescriptionProvider->getPaymentDescription($payment, $method, $order)->willReturn('description');
        $request->getSource()->willReturn($payment);
        $request->getTo()->willReturn('array');

        $request->getSource()->willReturn($payment);

        $paymentLocaleResolver->resolveFromOrder($order)->willReturn('payment_locale');
        $details = [
            'amount' => [
                'value' => '445535.00',
                'currency' => 'EUR',
            ],
            'description' => 'description',
            'metadata' => [
                'order_id' => 1,
                'customer_id' => 1,
                'molliePaymentMethods' => 15,
                'cartToken' => 'token',
                'selected_issuer' => null,
                'methodType' => 'Orders API',
            ],
            'full_name' => 'Jan Kowalski',
            'email' => 'shop@example.com',
            'locale' => 'payment_locale',
        ];
        $orderConverter->convert($order, $details, $divisor, $method)->willReturn($details);

        $request->setResult($details)->shouldBeCalled();

        $this->execute($request);
    }

    function it_supports_only_convert_request_with_get_source_as_instance_of_payment_interface_and_get_to_equals_array(
        Convert $request,
        PaymentInterface $payment
    ): void {
        $request->getSource()->willReturn($payment);
        $request->getTo()->willReturn('array');

        $this->supports($request)->shouldReturn(true);
    }
}
