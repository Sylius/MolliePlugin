<?php

/*
 * This file is part of the Sylius Mollie Plugin package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\MolliePlugin\DTO\MolliePayment;

class Metadata
{
    /**
     * @param int|null $orderId
     * @param string|null $customerId
     * @param string|null $molliePaymentMethods
     * @param string|null $cartToken
     * @param bool|null $saveCardInfo
     * @param bool|null $useSavedCards
     * @param string|null $selectedIssuer
     * @param string|null $methodType
     */
    public function __construct(private ?int  $orderId, private ?string $customerId, private ?string $molliePaymentMethods, private ?string $cartToken, private ?bool $saveCardInfo, private ?bool $useSavedCards, private ?string $selectedIssuer, private ?string $methodType)
    {
    }

    /**
     * @return int|null
     */
    public function getOrderId(): ?int
    {
        return $this->orderId;
    }

    /**
     * @param int|null $orderId
     * @return void
     */
    public function setOrderId(?int $orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * @return string|null
     */
    public function getCustomerId(): ?string
    {
        return $this->customerId;
    }

    /**
     * @param string|null $customerId
     * @return void
     */
    public function setCustomerId(?string $customerId)
    {
        $this->customerId = $customerId;
    }

    /**
     * @return string|null
     */
    public function getMolliePaymentMethods(): ?string
    {
        return $this->molliePaymentMethods;
    }

    /**
     * @param string|null $molliePaymentMethods
     * @return void
     */
    public function setMolliePaymentMethods(?string $molliePaymentMethods)
    {
        $this->molliePaymentMethods = $molliePaymentMethods;
    }

    /**
     * @return string|null
     */
    public function getCartToken(): ?string
    {
        return $this->cartToken;
    }

    /**
     * @param string|null $cartToken
     * @return void
     */
    public function setCartToken(?string $cartToken)
    {
        $this->cartToken = $cartToken;
    }

    /**
     * @return bool|null
     */
    public function getSaveCardInfo(): ?bool
    {
        return $this->saveCardInfo;
    }

    /**
     * @param bool|null $saveCardInfo
     * @return void
     */
    public function setSaveCardInfo(?bool $saveCardInfo)
    {
        $this->saveCardInfo = $saveCardInfo;
    }

    /**
     * @return bool|null
     */
    public function getUseSavedCards(): ?bool
    {
        return $this->useSavedCards;
    }

    /**
     * @param bool|null $useSavedCards
     * @return void
     */
    public function setUseSavedCards(?bool $useSavedCards)
    {
        $this->useSavedCards = $useSavedCards;
    }

    /**
     * @return string|null
     */
    public function getSelectedIssuer(): ?string
    {
        return $this->selectedIssuer;
    }

    /**
     * @param string|null $selectedIssuer
     * @return void
     */
    public function setSelectedIssuer(?string $selectedIssuer)
    {
        $this->selectedIssuer = $selectedIssuer;
    }

    /**
     * @return string|null
     */
    public function getMethodType(): ?string
    {
        return $this->methodType;
    }

    /**
     * @param string|null $methodType
     * @return void
     */
    public function setMethodType(?string $methodType)
    {
        $this->methodType = $methodType;
    }

    /**
     * @return array
     */
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
