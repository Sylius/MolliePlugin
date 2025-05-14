import { model } from './cartTableRecurringVariants';

document.addEventListener('DOMContentLoaded', () => {
    const cartVariantDetails = document.getElementById('cart-variant-details');

    if (!cartVariantDetails) {
        return;
    }

    const cartItemsTableRows = document.querySelectorAll('#sylius-order tr');
    const variantDetailElements = cartVariantDetails.querySelectorAll('div[data-recurring]');

    variantDetailElements.forEach((variantDetailsElement) => {
        const index = Number(variantDetailsElement.getAttribute('data-index'));
        const recurring = Number(variantDetailsElement.getAttribute('data-recurring'));
        const interval = variantDetailsElement.getAttribute('data-interval');
        const times = variantDetailsElement.getAttribute('data-times');

        if (recurring === 1) {
            const row = cartItemsTableRows[index + 1];
            if (!row) return;

            const cells = row.querySelectorAll('td');
            const [item, unitPrice, quantity, total] = cells;

            model.addRecurringDetailsLabels(item, total, interval, times);
        }
    });
});
