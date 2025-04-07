import {model} from './cartTableRecurringVariants';

$(function () {
    $(document).ready(function () {
        const __recurringContainer = $('#sylius-product-name').siblings('.ui.text.menu');
        const __productPriceContainer = $('#product-price');

        const getMatchSelector = () => {
            let selector = '';

            $('#sylius-product-adding-to-cart select[data-option]').each((index, element) => {
                const select = $(element);
                const option = select.find('option:selected').val();
                selector += `[data-${select.attr('data-option')}="${option}"]`;
            });

            return selector;
        }

        const isChoice = () => {
            return $('#sylius-product-adding-to-cart input[type="radio"][name="sylius_add_to_cart[cartItem][variant]"]').length > 0;
        }

        const resolveSelector = () => {
            return isChoice() ? getChoiceSelector() : getMatchSelector();
        }

        const getChoiceSelector = () => {
            return `[data-variant="${$('#sylius-product-adding-to-cart input[type="radio"][name="sylius_add_to_cart[cartItem][variant]"]:checked').val()}"]`;
        }

        const getTimes = () => {
            return $('#sylius-variants-recurring-times').find(resolveSelector()).attr('data-value');
        }

        const getInterval = () => {
            return $('#sylius-variants-recurring-interval').find(resolveSelector()).attr('data-value');
        }

        const checkRecurringMatch = () => {
            return '1' === $('#sylius-variants-recurring-match').find(resolveSelector()).attr('data-value');
        }

        const checkRecurringChoice = () => {
            return '1' === $('#sylius-variants-recurring-choice').find(resolveSelector()).attr('data-value');
        }

        const removeRecurringDetailsLabels = () => {
            model.clearLabels();
        };

        const addRecurringDetailsLabels = () => {
            // recurring label
            model.appendRecurringLabel(__recurringContainer);

            // times label
            model.appendTimesLabel(__recurringContainer, getTimes());

            model.appendIntervalLabel(__productPriceContainer, getInterval());
        };

        const updateProductRecurringLabel = () => {
            removeRecurringDetailsLabels();

            if (checkRecurringMatch() || checkRecurringChoice()) {
                addRecurringDetailsLabels();
            }
        }

        $('form[name=sylius_add_to_cart]').on('change', () => {
            updateProductRecurringLabel()
        });
        updateProductRecurringLabel();
    })
})
