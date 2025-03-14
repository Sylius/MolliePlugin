<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Resolver;

use Sylius\MolliePlugin\Client\MollieApiClient;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\MolliePlugin\Form\Type\MollieGatewayConfigurationType;
use Sylius\MolliePlugin\Logger\MollieLoggerActionInterface;
use Sylius\MolliePlugin\Repository\PaymentMethodRepositoryInterface;
use Mollie\Api\Exceptions\ApiException;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Resource\Exception\UpdateHandlingException;
use Webmozart\Assert\Assert;

final class MollieApiClientKeyResolver implements MollieApiClientKeyResolverInterface
{
    /** @var MollieApiClient */
    private $mollieApiClient;

    /** @var MollieLoggerActionInterface */
    private $loggerAction;

    /** @var PaymentMethodRepositoryInterface */
    private $paymentMethodRepository;

    /** @var ChannelContextInterface */
    private $channelContext;

    /** @var MollieFactoryNameResolverInterface */
    private $factoryNameResolver;

    public function __construct(
        MollieApiClient $mollieApiClient,
        MollieLoggerActionInterface $loggerAction,
        PaymentMethodRepositoryInterface $paymentMethodRepository,
        ChannelContextInterface $channelContext,
        MollieFactoryNameResolverInterface $factoryNameResolver
    ) {
        $this->mollieApiClient = $mollieApiClient;
        $this->loggerAction = $loggerAction;
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->channelContext = $channelContext;
        $this->factoryNameResolver = $factoryNameResolver;
    }

    public function getClientWithKey(OrderInterface $order = null): MollieApiClient
    {
        /** @var ChannelInterface $channel */
        $channel = $this->channelContext->getChannel();

        $paymentMethod = $this->paymentMethodRepository->findOneByChannelAndGatewayFactoryName(
            $channel,
            $this->factoryNameResolver->resolve($order)
        );

        if (null === $paymentMethod) {
            throw new UpdateHandlingException(sprintf('No payment method found'));
        }

        $gateway = $paymentMethod->getGatewayConfig();

        Assert::notNull($gateway);
        $config = $gateway->getConfig();

        $environment = true === $config['environment'] ?
            MollieGatewayConfigurationType::API_KEY_LIVE :
            MollieGatewayConfigurationType::API_KEY_TEST;

        try {
            /** @var MollieApiClient $mollieApiClient */
            $mollieApiClient = $this->mollieApiClient->setApiKey($config[$environment]);

            return $mollieApiClient;
        } catch (ApiException $e) {
            $this->loggerAction->addNegativeLog(sprintf('API call failed: %s', $e->getMessage()));

            throw new UpdateHandlingException(sprintf('API call failed: %s', htmlspecialchars($e->getMessage())));
        }
    }
}
