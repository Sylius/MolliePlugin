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

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\MolliePlugin\Mailer\Sender\PaymentLinkEmailSender;
use Sylius\MolliePlugin\Mailer\Sender\PaymentLinkEmailSenderInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->defaults()
        ->public();

    $services->set('sylius_mollie.mailer.sender.payment_link', PaymentLinkEmailSender::class)
        ->args([
            service('sylius.email_sender'),
            service('sylius_mollie.twig.parser.content'),
        ]);

    $services->alias(PaymentLinkEmailSenderInterface::class, 'sylius_mollie.mailer.sender.payment_link');
};
