<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Controller\Action\Admin;

use Sylius\MolliePlugin\Entity\MollieGatewayConfigInterface;
use Sylius\MolliePlugin\Entity\MollieMethodImageInterface;
use Sylius\MolliePlugin\Logger\MollieLoggerActionInterface;
use Sylius\MolliePlugin\Uploader\PaymentMethodLogoUploaderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Webmozart\Assert\Assert;

final class DeletePaymentMethodImage
{
    /** @var PaymentMethodLogoUploaderInterface */
    private $logoUploader;

    /** @var RepositoryInterface */
    private $logoRepository;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var MollieLoggerActionInterface */
    private $loggerAction;

    public function __construct(
        PaymentMethodLogoUploaderInterface $logoUploader,
        RepositoryInterface $logoRepository,
        EntityManagerInterface $entityManager,
        MollieLoggerActionInterface $loggerAction
    ) {
        $this->logoUploader = $logoUploader;
        $this->logoRepository = $logoRepository;
        $this->entityManager = $entityManager;
        $this->loggerAction = $loggerAction;
    }

    public function __invoke(Request $request): Response
    {
        $methodName = $request->request->get('method');

        /** @var MollieGatewayConfigInterface $mollieGateway */
        $mollieGateway = $this->logoRepository->findOneBy(['name' => $methodName]);

        /** @var MollieMethodImageInterface $customizeMethodImage */
        $customizeMethodImage = $mollieGateway->getCustomizeMethodImage();

        Assert::notNull($customizeMethodImage->getPath());
        $this->logoUploader->remove($customizeMethodImage->getPath());
        $mollieGateway->setCustomizeMethodImage(null);

        $this->entityManager->persist($mollieGateway);
        $this->entityManager->flush();

        $this->loggerAction->addLog(sprintf('Deleted image from method'));

        return new Response(Response::$statusTexts[Response::HTTP_OK], Response::HTTP_OK);
    }
}
