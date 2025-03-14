<?php


declare(strict_types=1);

namespace Tests\Sylius\MolliePlugin\Behat\Page\Admin\PaymentMethod;

use Sylius\Behat\Page\Admin\Crud\CreatePageInterface as BaseCreatePageInterface;

interface CreatePageInterface extends BaseCreatePageInterface
{
    public function setApiKey(string $apiKey): void;

    public function setProfileId(string $profileId): void;

    public function loadPaymentMethods(): void;

    public function enablePaymentMethod(string $paymentMethodName): void;

    public function containsErrorWithMessage(string $message, bool $strict = true): bool;

    public function containsSuccessMessage(string $message): bool;
}
