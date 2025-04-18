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

namespace Tests\Sylius\MolliePlugin\Unit\Cli;

use PHPUnit\Framework\TestCase;
use Sylius\MolliePlugin\Console\Command\SendAbandonedPaymentLink;
use Sylius\MolliePlugin\Creator\AbandonedPaymentLinkCreatorInterface;
use Symfony\Component\Console\Command\Command;

final class SendAbandonedPaymentLinkTest extends TestCase
{
    private AbandonedPaymentLinkCreatorInterface $abandonedPaymentLinkCreatorMock;

    private SendAbandonedPaymentLink $sendAbandonedPaymentLink;

    protected function setUp(): void
    {
        $this->abandonedPaymentLinkCreatorMock = $this->createMock(AbandonedPaymentLinkCreatorInterface::class);
        $this->sendAbandonedPaymentLink = new SendAbandonedPaymentLink($this->abandonedPaymentLinkCreatorMock);
    }

    public function testExtendsCommand(): void
    {
        $this->assertInstanceOf(Command::class, $this->sendAbandonedPaymentLink);
    }
}
