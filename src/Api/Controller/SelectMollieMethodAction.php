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
use Sylius\MolliePlugin\Creator\PaymentDataCreatorInterface;
use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\MolliePlugin\Entity\MollieCustomer;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\MolliePlugin\Entity\OrderInterface as MollieOrderInterface;
use Sylius\MolliePlugin\Factory\MollieSubscriptionFactoryInterface;
use Sylius\MolliePlugin\Logger\MollieLoggerActionInterface;
use Sylius\MolliePlugin\Payum\Checker\MollieGatewayFactoryCheckerInterface;
use Sylius\MolliePlugin\Payum\Factory\MollieSubscriptionGatewayFactory;
use Sylius\MolliePlugin\Repository\MollieSubscriptionRepositoryInterface;
use Sylius\MolliePlugin\Resolver\MollieApiClientKeyResolverInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class SelectMollieMethodAction
{
    use PayableOrderTrait;

    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly MollieApiClientKeyResolverInterface $apiClientKeyResolver,
        private readonly MollieGatewayFactoryCheckerInterface $mollieGatewayFactoryChecker,
        private readonly RepositoryInterface $mollieCustomerRepository,
        private readonly MollieSubscriptionFactoryInterface $subscriptionFactory,
        private readonly MollieSubscriptionRepositoryInterface $subscriptionRepository,
        private readonly PaymentDataCreatorInterface $paymentDataCreator,
        private readonly MollieLoggerActionInterface $logger,
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

        $paymentData = $this->paymentDataCreator->create(
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

        if (PaymentStatus::STATUS_PENDING === $existingMolliePayment->status) {
            $checkoutUrl = $existingMolliePayment->_links->checkout->href ?? null;

            if (null !== $checkoutUrl) {
                return new JsonResponse([
                    'methodId' => $existingMolliePayment->method ?? $methodId,
                    'checkoutUrl' => $checkoutUrl,
                ]);
            }

            return new JsonResponse(
                ['error' => 'A payment is already in progress for this order. Please wait for it to finish or expire.'],
                Response::HTTP_CONFLICT,
            );
        }

        if (PaymentStatus::STATUS_OPEN !== $existingMolliePayment->status) {
            return null;
        }

        $checkoutUrl = $existingMolliePayment->_links->checkout->href ?? null;

        if (null !== $checkoutUrl &&
            (null === $existingMolliePayment->method || $existingMolliePayment->method === $methodId)) {
            return new JsonResponse([
                'methodId' => $methodId,
                'checkoutUrl' => $checkoutUrl,
            ]);
        }

        // Cancellation is unavailable for some methods; those stay orphaned until expiry (#329).
        if (true === ($existingMolliePayment->isCancelable ?? false)) {
            try {
                $mollieApiClient->payments->cancel($existingMolliePayment->id);
            } catch (ApiException) {
            }
        }

        return null;
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
