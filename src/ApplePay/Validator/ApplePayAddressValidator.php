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

namespace Sylius\MolliePlugin\ApplePay\Validator;

use Webmozart\Assert\Assert;

final class ApplePayAddressValidator implements ApplePayAddressValidatorInterface
{
    public function validate(array $data): void
    {
        Assert::true(isset($data['locality']), 'The locality want provide');
        Assert::true(isset($data['addressLines']), 'The addressLines want provide');
        Assert::true(isset($data['postalCode']), 'The postalCode want provide');
        Assert::true(isset($data['countryCode']), 'The countryCode want provide');
        Assert::true(isset($data['givenName']), 'The givenName want provide');
        Assert::true(isset($data['familyName']), 'The familyName want provide');
    }
}
