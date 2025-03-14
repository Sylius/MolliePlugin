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

namespace Sylius\MolliePlugin\Controller\Action\Admin;

use Sylius\MolliePlugin\Creator\ChangePositionPaymentMethodCreatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ChangePositionPaymentMethodAction
{
    /** @var ChangePositionPaymentMethodCreatorInterface */
    private $changePositionPaymentMethodCreator;

    public function __construct(ChangePositionPaymentMethodCreatorInterface $changePositionPaymentMethodCreator)
    {
        $this->changePositionPaymentMethodCreator = $changePositionPaymentMethodCreator;
    }

    public function __invoke(Request $request): Response
    {
        $this->changePositionPaymentMethodCreator->createFromRequest($request);

        return new Response('OK');
    }
}
