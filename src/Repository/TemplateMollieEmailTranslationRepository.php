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

namespace Sylius\MolliePlugin\Repository;

use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\MolliePlugin\Entity\TemplateMollieEmailTranslationInterface;

class TemplateMollieEmailTranslationRepository extends EntityRepository implements TemplateMollieEmailTranslationRepositoryInterface
{
    public function findOneByLocaleCodeAdnType(string $localeCode, string $type): ?TemplateMollieEmailTranslationInterface
    {
        return $this->createQueryBuilder('tt')
            ->innerJoin('tt.translatable', 'templateEmail')
            ->where('tt.locale = :locale')
            ->andWhere('templateEmail.type = :type')
            ->setParameter('locale', $localeCode)
            ->setParameter('type', $type)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
