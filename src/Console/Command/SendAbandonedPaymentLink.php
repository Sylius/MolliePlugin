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

namespace Sylius\MolliePlugin\Console\Command;

use Sylius\MolliePlugin\Creator\AbandonedPaymentLinkCreatorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Stopwatch\Stopwatch;

class SendAbandonedPaymentLink extends Command
{
    public const COMMAND_NAME = 'mollie:send-payment-link';

    public const COMMAND_ID = 'mollie:send-payment-link';

    private SymfonyStyle $io;

    public function __construct(private readonly AbandonedPaymentLinkCreatorInterface $abandonedPaymentLinkCreator)
    {
        parent::__construct(self::COMMAND_NAME);
    }

    protected function configure(): void
    {
        $this->setDescription('Send payment link to customers');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $stopwatch = new Stopwatch();
        $stopwatch->start(self::COMMAND_ID);

        $this->io->title('Mollie - send link');

        try {
            $this->io->writeln('Exporting...');
            $this->abandonedPaymentLinkCreator->create();

            $this->io->success('Successfully send all payment links');
        } catch (\Exception $exception) {
            $this->io->error(\sprintf('An error has occurred during send payment link process. (%s)', $exception->getMessage()));

            return 1;
        }

        $event = $stopwatch->stop(self::COMMAND_ID);

        if ($output->isVerbose()) {
            $this->io->comment(\sprintf(
                'Duration: %.2f ms / Memory: %.2f MB',
                $event->getDuration(),
                $event->getMemory() / (1024 ** 2),
            ));
        }

        return 0;
    }
}
