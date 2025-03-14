<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Controller\Action\Admin;

use Sylius\MolliePlugin\Resolver\ApiKeysTestResolverInterface;
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
