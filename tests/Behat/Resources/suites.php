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

use Behat\Config\Config;

return (new Config())
    ->import([
        'suites/ui/managing_payment_method_mollie.php',
        'suites/ui/paying_with_mollie_for_order.php',
        'suites/ui/refunding_mollie_payment.php',
        'suites/ui/cancelling_mollie_subscription.php',
        'suites/ui/create_scheduled_mollie_subscription_payment.php',
    ])
;
