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

namespace spec\Sylius\MolliePlugin\Cli;

use Sylius\MolliePlugin\Cli\SendAbandonedPaymentLink;
use Sylius\MolliePlugin\Creator\AbandonedPaymentLinkCreatorInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Console\Command\Command;

final class SendAbandonedPaymentLinkSpec extends ObjectBehavior
{
    function let(
        AbandonedPaymentLinkCreatorInterface $abandonedPaymentLinkCreator
    ): void {
        $this->beConstructedWith($abandonedPaymentLinkCreator);
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(SendAbandonedPaymentLink::class);
    }

    function it_should_have_extends_command(): void
    {
        $this->shouldBeAnInstanceOf(Command::class);
    }
}
