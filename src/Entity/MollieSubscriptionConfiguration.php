<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Entity;

class MollieSubscriptionConfiguration implements MollieSubscriptionConfigurationInterface
{
    protected ?int $id = null;

    protected string $hostName = '';

    protected ?int $port = null;

    protected ?string $subscriptionId = null;

    protected ?string $mandateId = null;

    protected ?string $customerId = null;

    protected ?string $interval = null;

    protected int $numberOfRepetitions = 1;

    protected array $paymentDetailsConfiguration = [];

    protected MollieSubscriptionInterface $subscription;

    public function __construct(MollieSubscriptionInterface $subscription)
    {
        $this->subscription = $subscription;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHostName(): string
    {
        return $this->hostName;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    public function getSubscriptionId(): ?string
    {
        return $this->subscriptionId;
    }

    public function getMandateId(): ?string
    {
        return $this->mandateId;
    }

    public function getCustomerId(): ?string
    {
        return $this->customerId;
    }

    public function getInterval(): ?string
    {
        return $this->interval;
    }

    public function getNumberOfRepetitions(): int
    {
        return $this->numberOfRepetitions;
    }

    public function getPaymentDetailsConfiguration(): array
    {
        return $this->paymentDetailsConfiguration;
    }

    public function getSubscription(): MollieSubscriptionInterface
    {
        return $this->subscription;
    }

    public function setHostName(string $hostName): void
    {
        $this->hostName = $hostName;
    }

    public function setPort(?int $port): void
    {
        $this->port = $port;
    }

    public function setSubscriptionId(?string $subscriptionId): void
    {
        $this->subscriptionId = $subscriptionId;
    }

    public function setMandateId(?string $mandateId): void
    {
        $this->mandateId = $mandateId;
    }

    public function setCustomerId(?string $customerId): void
    {
        $this->customerId = $customerId;
    }

    public function setInterval(?string $interval): void
    {
        $this->interval = $interval;
    }

    public function setNumberOfRepetitions(int $numberOfRepetitions): void
    {
        $this->numberOfRepetitions = $numberOfRepetitions;
    }

    public function setPaymentDetailsConfiguration(array $paymentDetailsConfiguration): void
    {
        $this->paymentDetailsConfiguration = $paymentDetailsConfiguration;
    }
}
