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
