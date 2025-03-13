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

namespace SyliusMolliePlugin\Controller\Action\Admin;

use SyliusMolliePlugin\Resolver\ApiKeysTestResolverInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

final class TestApiKeysAction
{
    /** @var ApiKeysTestResolverInterface */
    private $apiKeysTestResolver;

    /** @var Environment */
    private $twig;

    public function __construct(
        ApiKeysTestResolverInterface $apiKeysTestResolver,
        Environment $twig
    ) {
        $this->apiKeysTestResolver = $apiKeysTestResolver;
        $this->twig = $twig;
    }

    public function __invoke(Request $request): Response
    {
        $data = $this->apiKeysTestResolver->fromRequest($request);

        return new Response($this->twig->render(
            '@SyliusMolliePlugin/Admin/PaymentMethod/testApiKeys.html.twig',
            [
                'tests' => $data,
            ]
        ));
    }
}
