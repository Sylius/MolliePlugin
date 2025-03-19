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

namespace Tests\Sylius\MolliePlugin\Behat\Page\Shop\Checkout;

use Behat\Mink\Element\NodeElement;
use Sylius\Behat\Page\Shop\Checkout\CompletePage as BaseCompletePage;
use Webmozart\Assert\Assert;

final class CompletePage extends BaseCompletePage implements CompletePageInterface
{
    public function selectPaymentOption(string $paymentOptionName): void
    {
        $paymentOptionElements = $this->getDocument()->findAll('css', '[data-test-payment-method-payment-item]');

        /** @var NodeElement $paymentOptionElement */
        foreach ($paymentOptionElements as $paymentOptionElement) {
            if ($paymentOptionElement->getAttribute('value') === $paymentOptionName) {
                $elementNameAttribute = $paymentOptionElement->getAttribute('name');

                Assert::notNull($elementNameAttribute);
                $this->getDocument()->selectFieldOption($elementNameAttribute, $paymentOptionName);
            }
        }
    }
}
