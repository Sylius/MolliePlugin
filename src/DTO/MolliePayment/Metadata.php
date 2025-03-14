<?php

namespace Sylius\MolliePlugin\DTO\MolliePayment;

class Metadata
{
    /**
     * @var int|null
     */
    private ?int $orderId;
    /**
     * @var string|null
     */
    private ?string $customerId;
    /**
     * @var string|null
     */
    private ?string $molliePaymentMethods;
    /**
     * @var string|null
     */
    private ?string $cartToken;
    /**
     * @var bool|null
     */
    private ?bool $saveCardInfo;
    /**
     * @var bool|null
     */
    private ?bool $useSavedCards;
    /**
     * @var string|null
     */
    private ?string $selectedIssuer;
    /**
     * @var string|null
     */
    private ?string $methodType;

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
    public function __construct(
        ?int  $orderId,
        ?string $customerId,
        ?string $molliePaymentMethods,
        ?string $cartToken,
        ?bool $saveCardInfo,
        ?bool $useSavedCards,
        ?string $selectedIssuer,
        ?string $methodType
    )
    {
        $this->orderId = $orderId;
        $this->customerId = $customerId;
        $this->molliePaymentMethods = $molliePaymentMethods;
        $this->cartToken = $cartToken;
        $this->saveCardInfo = $saveCardInfo;
        $this->useSavedCards = $useSavedCards;
        $this->selectedIssuer = $selectedIssuer;
        $this->methodType = $methodType;
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
