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

namespace Sylius\MolliePlugin\Validator\Constraints;

use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class MollieGatewayConfigValidator extends ConstraintValidator
{
    private const MINIMUM_FIELD = 'minimumAmount';

    private const MAXIMUM_FIELD = 'maximumAmount';

    private const AMOUNT_LIMITS_FIELD = 'amountLimits';

    private const FIELD_VALUE = 'value';

    public function validate($value, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, MollieGatewayConfigValidatorType::class);

        if ($value instanceof PersistentCollection) {
            $this->validateAmounts($value, $constraint);
        }
    }

    private function validateAmounts(PersistentCollection $collection, MollieGatewayConfigValidatorType $constraint): void
    {
        $mollieGatewayConfigs = $collection->getSnapshot();
        foreach ($mollieGatewayConfigs as $key => $mollieGatewayConfig) {
            $amountLimits = $mollieGatewayConfig->getAmountLimits();
            if (!$amountLimits) {
                continue;
            }

            $minAmount = $amountLimits->getMinimumAmount();
            $maxAmount = $amountLimits->getMaximumAmount();

            if ($minAmount !== null && $maxAmount !== null && $minAmount > $maxAmount) {
                $this->context->buildViolation($constraint->minGreaterThanMaxMessage)
                    ->atPath("[{$key}]." . self::AMOUNT_LIMITS_FIELD . '.' . self::MAXIMUM_FIELD)
                    ->addViolation();
            }

            if ($minAmount !== null && $mollieGatewayConfig->getMinimumAmount()) {
                $mollieMinAmount = $mollieGatewayConfig->getMinimumAmount()[self::FIELD_VALUE];
                if ($mollieMinAmount !== null && $mollieMinAmount > $minAmount) {
                    $this->context->buildViolation($constraint->minLessThanMollieMinMessage)
                        ->setParameter('%amount%', $mollieMinAmount)
                        ->atPath("[{$key}]." . self::AMOUNT_LIMITS_FIELD . '.' . self::MINIMUM_FIELD)
                        ->addViolation();
                }
            }

            if ($maxAmount !== null && $mollieGatewayConfig->getMaximumAmount()) {
                $mollieMaxAmount = $mollieGatewayConfig->getMaximumAmount()[self::FIELD_VALUE];
                if ($mollieMaxAmount !== null && $mollieMaxAmount < $maxAmount) {
                    $this->context->buildViolation($constraint->maxGreaterThanMollieMaxMessage)
                        ->setParameter('%amount%', $mollieMaxAmount)
                        ->atPath("[{$key}]." . self::AMOUNT_LIMITS_FIELD . '.' . self::MAXIMUM_FIELD)
                        ->addViolation();
                }
            }
        }
    }
}
