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

namespace Sylius\MolliePlugin\Controller\Admin;

use Sylius\MolliePlugin\Updater\MolliePaymentMethodPositionUpdaterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/** @phpstan-import-type PositionsArray from MolliePaymentMethodPositionUpdaterInterface */
final class ChangePositionPaymentMethodAction
{
    public function __construct(private readonly MolliePaymentMethodPositionUpdaterInterface $positionUpdater)
    {
    }

    public function __invoke(Request $request): Response
    {
        /** @var PositionsArray $positions */
        $positions = $this->filterEmptyPositions($request->get('data', []));

        if ($positions !== []) {
            $this->positionUpdater->update($positions);
        }

        return new Response('OK');
    }

    /**
     * @param array<int, array{
     *      name: string,
     *      identifier: int|string,
     *      id?: string|null
     *  }> $positions
     *
     * @return PositionsArray
     */
    private function filterEmptyPositions(array $positions): array
    {
        return array_filter(
            $positions,
            static fn (array $position): bool => isset($position['id']) && '' !== $position['id'],
        );
    }
}
