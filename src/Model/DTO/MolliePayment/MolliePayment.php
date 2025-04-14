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

namespace Sylius\MolliePlugin\Model\DTO\MolliePayment;

/**
 * @phpstan-import-type AmountArray from Amount
 * @phpstan-import-type MetadataArray from Metadata
 *
 * @phpstan-type MolliePaymentArray array{
 *      amount: AmountArray,
 *      method: string|null,
 *      description: string|null,
 *      issuer: string|null,
 *      metadata: MetadataArray,
 *      customerId: string|null,
 *      locale: string|null,
 *      redirectUrl: string|null,
 *      webhookUrl: string|null,
 * }
 */
class MolliePayment
{
    private Amount $amount;

    private ?string $description = null;

    private Metadata $metadata;

    private ?string $customerId = null;

    private ?string $locale = null;

    private ?string $method = null;

    private ?string $webhookUrl = null;

    private ?string $redirectUrl = null;

    private ?string $issuer = null;

    public function getAmount(): Amount
    {
        return $this->amount;
    }

    public function setAmount(Amount $amount): void
    {
        $this->amount = $amount;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getMetadata(): Metadata
    {
        return $this->metadata;
    }

    public function setMetadata(Metadata $metadata): void
    {
        $this->metadata = $metadata;
    }

    public function getCustomerId(): ?string
    {
        return $this->customerId;
    }

    public function setCustomerId(?string $customerId): void
    {
        $this->customerId = $customerId;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(?string $locale): void
    {
        $this->locale = $locale;
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function setMethod(?string $method): void
    {
        $this->method = $method;
    }

    public function getWebhookUrl(): ?string
    {
        return $this->webhookUrl;
    }

    public function setWebhookUrl(?string $webhookUrl): void
    {
        $this->webhookUrl = $webhookUrl;
    }

    public function getRedirectUrl(): ?string
    {
        return $this->redirectUrl;
    }

    public function setRedirectUrl(?string $redirectUrl): void
    {
        $this->redirectUrl = $redirectUrl;
    }

    public function getIssuer(): ?string
    {
        return $this->issuer;
    }

    public function setIssuer(?string $issuer): void
    {
        $this->issuer = $issuer;
    }

    /** @return MolliePaymentArray */
    public function toArray(): array
    {
        return [
            'amount' => $this->getAmount()->toArray(),
            'method' => $this->getMethod(),
            'description' => $this->getDescription(),
            'issuer' => $this->getIssuer(),
            'metadata' => $this->getMetadata()->toArray(),
            'customerId' => $this->getCustomerId(),
            'locale' => $this->getLocale(),
            'redirectUrl' => $this->getRedirectUrl(),
            'webhookUrl' => $this->getWebhookUrl(),
        ];
    }
}
