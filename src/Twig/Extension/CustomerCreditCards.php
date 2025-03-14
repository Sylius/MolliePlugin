<?php

/*
 * This file is part of the Sylius Mollie Plugin package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SyliusMolliePlugin\Twig\Extension;

use Sylius\Component\Customer\Context\CustomerContextInterface;
use SyliusMolliePlugin\Client\MollieApiClient;
use SyliusMolliePlugin\Repository\CustomerRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class CustomerCreditCards extends AbstractExtension
{
    /** @var MollieApiClient */
    private $apiClient;

    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @var CustomerContextInterface
     */
    private $customerContext;

    public function __construct(MollieApiClient $apiClient, EntityRepository $customerRepository, CustomerContextInterface $customerContext)
    {
        $this->apiClient = $apiClient;
        $this->customerRepository = $customerRepository;
        $this->customerContext = $customerContext;
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('isCardSaved', [$this, 'isCardSaved']),
            new TwigFunction('getCustomerFromContext', [$this, 'getCustomerFromContext'])
        ];
    }

    /**
     * @param string $customerEmail
     *
     * @return bool
     */
    public function isCardSaved(string $customerEmail)
    {
        $existingCustomer = $this->customerRepository->findOneBy(['email' => $customerEmail]);

        if ($existingCustomer) {
            return $existingCustomer->isCreditCardSaved() === '1';
        }

        return false;
    }

    /**
     * @return \Sylius\Component\Customer\Model\CustomerInterface|null
     */
    public function getCustomerFromContext()
    {
        return $this->customerContext->getCustomer();
    }
}
