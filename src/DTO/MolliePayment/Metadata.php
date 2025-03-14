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

namespace Sylius\MolliePlugin\DTO\MolliePayment;

class Metadata
{
    public function __construct(private ?int $orderId, private ?string $customerId, private ?string $molliePaymentMethods, private ?string $cartToken, private ?bool $saveCardInfo, private ?bool $useSavedCards, private ?string $selectedIssuer, private ?string $methodType)
    {
    }

    public function getOrderId(): ?int
    {
        return $this->orderId;
    }

    public function setOrderId(?int $orderId)
    {
        $this->orderId = $orderId;
    }

    public function getCustomerId(): ?string
    {
        return $this->customerId;
    }

    public function setCustomerId(?string $customerId)
    {
        $this->customerId = $customerId;
    }

    public function getMolliePaymentMethods(): ?string
    {
        return $this->molliePaymentMethods;
    }

    public function setMolliePaymentMethods(?string $molliePaymentMethods)
    {
        $this->molliePaymentMethods = $molliePaymentMethods;
    }

    public function getCartToken(): ?string
    {
        return $this->cartToken;
    }

    public function setCartToken(?string $cartToken)
    {
        $this->cartToken = $cartToken;
    }

    public function getSaveCardInfo(): ?bool
    {
        return $this->saveCardInfo;
    }

    public function setSaveCardInfo(?bool $saveCardInfo)
    {
        $this->saveCardInfo = $saveCardInfo;
    }

    public function getUseSavedCards(): ?bool
    {
        return $this->useSavedCards;
    }

    public function setUseSavedCards(?bool $useSavedCards)
    {
        $this->useSavedCards = $useSavedCards;
    }

    public function getSelectedIssuer(): ?string
    {
        return $this->selectedIssuer;
    }

    public function setSelectedIssuer(?string $selectedIssuer)
    {
        $this->selectedIssuer = $selectedIssuer;
    }

    public function getMethodType(): ?string
    {
        return $this->methodType;
    }

    public function setMethodType(?string $methodType)
    {
        $this->methodType = $methodType;
    }

    public function toArray(): array
    {
        return [
            'order_id' => $this->getOrderId(),
            'customer_id' => $this->getCustomerId(),
            'molliePaymentMethods' => $this->getMolliePaymentMethods(),
            'cartToken' => $this->getCartToken(),
            'saveCardInfo' => $this->getSaveCardInfo(),
            'useSavedCards' => $this->getUseSavedCards(),
            'selected_issuer' => $this->getSelectedIssuer(),
            'methodType' => $this->getMethodType(),
        ];
    }
}
