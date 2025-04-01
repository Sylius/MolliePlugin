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

namespace Tests\Sylius\MolliePlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Order\Model\OrderItemInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\MolliePlugin\Entity\MollieCustomer;
use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;
use Sylius\MolliePlugin\Entity\MollieSubscriptionScheduleInterface;
use Sylius\MolliePlugin\Subscription\Processor\SubscriptionScheduleProcessorInterface;
use Webmozart\Assert\Assert;

final class SubscriptionContext implements Context
{
    private EntityManagerInterface $entityManagerSubscription;

    private FactoryInterface $subscriptionFactory;

    private SharedStorageInterface $sharedStorage;

    private SubscriptionScheduleProcessorInterface $subscriptionScheduleProcessor;

    private string $output = '';

    public function __construct(
        EntityManagerInterface $entityManagerSubscription,
        FactoryInterface $subscriptionFactory,
        SharedStorageInterface $sharedStorage,
        SubscriptionScheduleProcessorInterface $subscriptionScheduleProcessor,
    ) {
        $this->entityManagerSubscription = $entityManagerSubscription;
        $this->subscriptionFactory = $subscriptionFactory;
        $this->sharedStorage = $sharedStorage;
        $this->subscriptionScheduleProcessor = $subscriptionScheduleProcessor;
    }

    /**
     * @Given /^(this order) has an active mollie subscription$/
     */
    public function thisOrderHasAnActiveMollieSubscription(OrderInterface $order): void
    {
        /** @var PaymentInterface $firstPayment */
        $firstPayment = $order->getPayments()->first();
        $mollieCustomer = new MollieCustomer();
        $mollieCustomer->setEmail('sylius@example.com');
        $mollieCustomer->setProfileId('cst_profile');

        $customer = $order->getCustomer();
        Assert::notNull($customer);

        $customerId = $customer->getId();

        $paymentDetails = [
            'email' => 'sylius@example.com',
            'amount' => [
                'value' => '1.00',
                'currency' => 'USD',
            ],
            'backurl' => 'http://127.0.0.1:8080/payment/capture/token',
            'metadata' => [
                'gateway' => 'mollie_subscription',
                'order_id' => $order->getId(),
                'cartToken' => null,
                'customer_id' => $customerId,
                'refund_token' => 'refundToken',
                'sequenceType' => 'first',
                'selected_issuer' => null,
                'molliePaymentMethods' => 'creditcard',
            ],
            'full_name' => 'First Last',
            'customerId' => $mollieCustomer->getProfileId(),
            'webhookUrl' => 'http://127.0.0.1:8080/payment/notify/token',
            'description' => 'Description',
            'cancel_token' => 'cancelToken',
            'sequenceType' => 'first',
            'payment_mollie_id' => 'tr_abc123',
            'customer_mollie_id' => $mollieCustomer->getProfileId(),
        ];

        $firstPayment->setDetails($paymentDetails);
        /** @var MollieSubscriptionInterface $subscription */
        $subscription = $this->subscriptionFactory->createNew();

        $subscription->addOrder($order);
        $subscription->setState(MollieSubscriptionInterface::STATE_ACTIVE);
        $subscriptionConfiguration = $subscription->getSubscriptionConfiguration();
        $subscriptionConfiguration->setInterval('10 days');

        $orderItem = $order->getItems()->first();
        Assert::isInstanceOf($orderItem, OrderItemInterface::class);
        $subscription->setOrderItem($orderItem);
        $subscription->setPaymentState(MollieSubscriptionInterface::PAYMENT_STATE_OK);
        $subscriptionConfiguration = $subscription->getSubscriptionConfiguration();
        $subscriptionConfiguration->setInterval('1 days');
        $subscriptionConfiguration->setNumberOfRepetitions(3);
        $subscriptionConfiguration->setPaymentDetailsConfiguration($paymentDetails);

        $this->entityManagerSubscription->persist($subscription);
        $this->entityManagerSubscription->persist($subscriptionConfiguration);
        $this->entityManagerSubscription->persist($mollieCustomer);
        $this->entityManagerSubscription->flush();

        $this->sharedStorage->set('subscription', $subscription);
    }

    /**
     * @Given /^(this subscription) has an active schedule$/
     */
    public function thisSubscriptionHasAnActiveSchedule(MollieSubscriptionInterface $subscription): void
    {
        $this->subscriptionScheduleProcessor
            ->processScheduleGeneration($subscription);

        $yesterdayDateTime = new \DateTime(date('d M Y H:i:s', strtotime('-1 days')));

        /** @var MollieSubscriptionScheduleInterface $schedule */
        foreach ($subscription->getSchedules() as $schedule) {
            $schedule->setScheduledDate($yesterdayDateTime);
        }

        $this->entityManagerSubscription->persist($subscription);
        $this->entityManagerSubscription->flush();
    }

    /**
     * @When I run command :command
     */
    public function iRunCommand(string $command): void
    {
        $output = shell_exec($command);
        Assert::string($output);
        $this->output = $output;
    }

    /**
     * @Then I should see :output in the output
     */
    public function iShouldSeeInTheOutput(string $output): void
    {
        if (!str_contains($this->output, $output)) {
            throw new \Exception(sprintf('Did not see "%s" in output "%s"', $output, $this->output));
        }
    }
}
