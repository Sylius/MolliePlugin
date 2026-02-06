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

namespace Sylius\MolliePlugin\Api\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Types\PaymentStatus;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\MolliePlugin\Client\MollieApiClient;
use Sylius\MolliePlugin\Converter\IntToStringConverterInterface;
use Sylius\MolliePlugin\Converter\OrderConverterInterface;
use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\MolliePlugin\Entity\MollieCustomer;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\MolliePlugin\Entity\OrderInterface as MollieOrderInterface;
use Sylius\MolliePlugin\Factory\MollieSubscriptionFactoryInterface;
use Sylius\MolliePlugin\Logger\MollieLoggerActionInterface;
use Sylius\MolliePlugin\Payum\Checker\MollieGatewayFactoryCheckerInterface;
use Sylius\MolliePlugin\Payum\Factory\MollieSubscriptionGatewayFactory;
use Sylius\MolliePlugin\Provider\DivisorProviderInterface;
use Sylius\MolliePlugin\Provider\PaymentDescriptionProviderInterface;
use Sylius\MolliePlugin\Repository\MollieGatewayConfigRepositoryInterface;
use Sylius\MolliePlugin\Repository\MollieSubscriptionRepositoryInterface;
use Sylius\MolliePlugin\Resolver\MollieApiClientKeyResolverInterface;
use Sylius\MolliePlugin\Resolver\PaymentLocaleResolverInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class SelectMollieMethodAction
{
    use PayableOrderTrait;

    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly IntToStringConverterInterface $intToStringConverter,
        private readonly UrlGeneratorInterface $router,
        private readonly MollieApiClientKeyResolverInterface $apiClientKeyResolver,
        private readonly MollieGatewayFactoryCheckerInterface $mollieGatewayFactoryChecker,
        private readonly PaymentDescriptionProviderInterface $paymentDescriptionProvider,
        private readonly PaymentLocaleResolverInterface $paymentLocaleResolver,
        private readonly DivisorProviderInterface $divisorProvider,
        private readonly MollieGatewayConfigRepositoryInterface $mollieGatewayConfigRepository,
        private readonly RepositoryInterface $mollieCustomerRepository,
        private readonly MollieSubscriptionFactoryInterface $subscriptionFactory,
        private readonly MollieSubscriptionRepositoryInterface $subscriptionRepository,
        private readonly MollieLoggerActionInterface $logger,
        private readonly OrderConverterInterface $orderConverter,
    ) {
    }

    public function __invoke(string $tokenValue, Request $request): JsonResponse
    {
        $order = $this->orderRepository->findOneByTokenValue($tokenValue);
        if (!$order instanceof OrderInterface) {
            throw new NotFoundHttpException(sprintf('Order with token "%s" not found', $tokenValue));
        }

        $this->assertOrderIsPayable($order);

        $data = json_decode($request->getContent(), true);

        $methodId = $data['methodId'] ?? null;
        $backUrl = $data['backUrl'] ?? null;
        if (empty($methodId) || empty($backUrl)) {
            throw new BadRequestHttpException('The `methodId` and `backUrl` are required');
        }

        $payment = $order->getLastPayment();
        if (!$payment instanceof PaymentInterface) {
            throw new NotFoundHttpException('No payment found');
        }

        $paymentMethod = $payment->getMethod();
        if (!$paymentMethod instanceof PaymentMethodInterface) {
            throw new NotFoundHttpException('No payment method found');
        }

        $gatewayConfig = $paymentMethod->getGatewayConfig();
        if (!$gatewayConfig instanceof GatewayConfigInterface) {
            throw new NotFoundHttpException('No gateway config found');
        }

        if (!$this->mollieGatewayFactoryChecker->isMollieGateway($gatewayConfig)) {
            throw new BadRequestException('The payment method is not using Mollie');
        }

        $mollieApiClient = $this->apiClientKeyResolver->getClientWithKey($order);

        $existingPaymentResponse = $this->handleExistingMolliePayment($mollieApiClient, $payment, $methodId);
        if (null !== $existingPaymentResponse) {
            return $existingPaymentResponse;
        }

        $customerMollieId = null;
        $isSubscription = $gatewayConfig->getFactoryName() === MollieSubscriptionGatewayFactory::FACTORY_NAME;

        if ($isSubscription) {
            $customerMollieId = $this->findOrCreateMollieCustomer($mollieApiClient, $order);
        }

        $paymentData = $this->createPaymentData(
            $order,
            $payment,
            $gatewayConfig,
            $methodId,
            $isSubscription,
            $backUrl,
            $customerMollieId,
        );

        try {
            $molliePayment = $mollieApiClient->payments->create($paymentData);

            $checkoutUrl = $molliePayment->_links->checkout->href ?? null;
            if (!$checkoutUrl) {
                $this->logger->addNegativeLog(sprintf('No checkout URL returned from Mollie for order %s', $order->getNumber()));

                return new JsonResponse(
                    ['error' => 'No checkout URL returned from Mollie'],
                    Response::HTTP_INTERNAL_SERVER_ERROR,
                );
            }
        } catch (ApiException $exception) {
            $this->logger->addNegativeLog(sprintf(
                'Creating payment in Mollie failed for order %s with: %s',
                $order->getNumber(),
                $exception->getMessage(),
            ));

            return new JsonResponse(
                ['error' => 'Creating payment in Mollie failed.'],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }

        $details = [
            'molliePaymentMethods' => $methodId,
            'payment_mollie_id' => $molliePayment->id,
            'cartToken' => null,
            'saveCardInfo' => '0',
            'useSavedCards' => '0',
            'webhookUrl' => $paymentData['webhookUrl'],
            'backurl' => $paymentData['redirectUrl'],
        ];

        if ($isSubscription) {
            $details['customer_mollie_id'] = $customerMollieId;
        }

        $payment->setDetails($details);

        if ($isSubscription) {
            $this->createInternalSubscriptions($order, $paymentData, $customerMollieId);
        }

        $this->entityManager->flush();

        return new JsonResponse([
            'methodId' => $methodId,
            'checkoutUrl' => $checkoutUrl,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function createPaymentData(
        OrderInterface $order,
        PaymentInterface $payment,
        GatewayConfigInterface $gatewayConfig,
        string $methodId,
        bool $isSubscription,
        string $backUrl,
        ?string $customerMollieId,
    ): array {
        $divisor = $this->divisorProvider->getDivisor();

        $methodConfig = $this->mollieGatewayConfigRepository->findOneActiveByGatewayNameAndMethod(
            $gatewayConfig->getGatewayName(),
            $methodId,
        );
        if (null === $methodConfig) {
            throw new BadRequestHttpException(sprintf('Mollie method "%s" cannot be selected', $methodId));
        }

        $webhookUrl = $this->router->generate(
            'sylius_mollie_shop_payment_webhook',
            ['_locale' => $order->getLocaleCode() ?? 'en_US'],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );

        $paymentData = [
            'method' => $methodId,
            'amount' => [
                'currency' => $payment->getCurrencyCode(),
                'value' => $this->intToStringConverter->convertIntToString($payment->getAmount(), $divisor),
            ],
            'description' => $this->paymentDescriptionProvider->getPaymentDescription($payment, $methodConfig, $order),
            'redirectUrl' => $backUrl,
            'webhookUrl' => $webhookUrl,
            'metadata' => [
                'order_id' => $order->getId(),
                'customer_id' => $order->getCustomer()?->getId(),
                'molliePaymentMethods' => $methodId,
            ],
        ];

        $converted = $this->orderConverter->convert($order, $paymentData, $divisor, $methodConfig);
        $paymentData['billingAddress'] = $converted['billingAddress'];
        $paymentData['shippingAddress'] = $converted['shippingAddress'];
        $paymentData['lines'] = array_map(
            static fn (array $line): array => [
                'description' => $line['name'] ?? '',
                'type' => $line['type'] ?? 'physical',
                'quantity' => $line['quantity'],
                'unitPrice' => $line['unitPrice'],
                'totalAmount' => $line['totalAmount'],
                'vatRate' => $line['vatRate'],
                'vatAmount' => $line['vatAmount'],
            ],
            $converted['lines'],
        );

        $locale = $this->paymentLocaleResolver->resolveFromOrder($order);

        if ($locale !== null) {
            $paymentData['locale'] = $locale;
        }

        if ($isSubscription) {
            $paymentData['customerId'] = $customerMollieId;
            $paymentData['sequenceType'] = 'first';
            $paymentData['metadata']['sequenceType'] = 'first';
        }

        return $paymentData;
    }

    private function handleExistingMolliePayment(
        MollieApiClient $mollieApiClient,
        PaymentInterface $payment,
        string $methodId,
    ): ?JsonResponse {
        $details = $payment->getDetails();
        $existingMolliePaymentId = $details['payment_mollie_id'] ?? null;

        if ($existingMolliePaymentId === null) {
            return null;
        }

        try {
            $existingMolliePayment = $mollieApiClient->payments->get($existingMolliePaymentId);
        } catch (ApiException) {
            return null;
        }

        if (!in_array(
            $existingMolliePayment->status,
            [PaymentStatus::STATUS_OPEN, PaymentStatus::STATUS_PENDING],
            true,
        )) {
            return null;
        }

        $checkoutUrl = $existingMolliePayment->_links->checkout->href ?? null;
        if (
            null === $checkoutUrl ||
            $methodId !== $existingMolliePayment->method
        ) {
            return null;
        }

        return new JsonResponse([
            'methodId' => $methodId,
            'checkoutUrl' => $checkoutUrl,
        ]);
    }

    private function findOrCreateMollieCustomer(MollieApiClient $mollieApiClient, OrderInterface $order): string
    {
        $email = $order->getCustomer()?->getEmail();

        /** @var ?MollieCustomer $customer */
        $customer = $this->mollieCustomerRepository->findOneBy(['email' => $email]);
        if (null === $customer) {
            $customer = new MollieCustomer();
            $customer->setEmail($email);
        }

        if (null === $customer->getProfileId()) {
            $customerMollie = $mollieApiClient->customers->create([
                'name' => $order->getCustomer()?->getFullName(),
                'email' => $email,
            ]);
            $customer->setProfileId($customerMollie->id);

            $this->mollieCustomerRepository->add($customer);
        }

        return $customer->getProfileId();
    }

    /**
     * @param array<string, mixed> $paymentData
     */
    private function createInternalSubscriptions(
        MollieOrderInterface $order,
        array $paymentData,
        ?string $customerMollieId,
    ): void {
        foreach ($order->getRecurringItems() as $item) {
            $subscription = $this->subscriptionFactory->createFromFirstOrderWithOrderItemAndPaymentConfiguration(
                $order,
                $item,
                $paymentData,
            );
            $subscription->getSubscriptionConfiguration()->setCustomerId($customerMollieId);
            $this->subscriptionRepository->add($subscription);
        }
    }
}
