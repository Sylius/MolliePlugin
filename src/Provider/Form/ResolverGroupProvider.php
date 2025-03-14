<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Provider\Form;

use Sylius\MolliePlugin\Entity\ProductVariantInterface;
use Symfony\Component\Form\FormInterface;

final class ResolverGroupProvider implements ResolverGroupProviderInterface
{
    public function provide(FormInterface $form): array
    {
        $groups = ['sylius'];
        $data = $form->getData();

        if (false === $data instanceof ProductVariantInterface) {
            return $groups;
        }

        if (false === $data->isRecurring()) {
            $groups[] = 'non_recurring_product_variant';

            return $groups;
        }

        $groups[] = 'recurring_product_variant';

        return $groups;
    }
}
