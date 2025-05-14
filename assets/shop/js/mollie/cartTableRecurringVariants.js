export const model = {
    appendIntervalLabel: (container, interval) => {
        const __everyDaysLabel = document.getElementById('sylius-variants-recurring-interval-days').textContent;
        const __everyWeeksLabel = document.getElementById('sylius-variants-recurring-interval-weeks').textContent;
        const __everyMonthsLabel = document.getElementById('sylius-variants-recurring-interval-months').textContent;

        const [amount, step] = interval.split(/\s+/g);
        let everyLabel = '';

        switch (step) {
            case 'days':
                everyLabel = __everyDaysLabel;
                break;
            case 'weeks':
                everyLabel = __everyWeeksLabel;
                break;
            case 'months':
                everyLabel = __everyMonthsLabel;
                break;
        }

        if (everyLabel !== '') {
            const html = document.getElementById('sylius-variants-recurring-interval-label').innerHTML;
            const __intervalElementContainer = document.createElement('div');
            __intervalElementContainer.innerHTML = html;
            __intervalElementContainer.textContent = everyLabel.replace(/\%amount\%/, amount);

            const __everyLabel = document.createElement('div');
            __everyLabel.id = 'every-label';
            __everyLabel.className = 'item mollie-every-label-container';
            __everyLabel.appendChild(__intervalElementContainer);

            container.appendChild(__everyLabel);
        }
    },

    appendRecurringLabel: (container) => {
        const html = document.getElementById('sylius-variants-recurring-label').innerHTML;
        const __prefixLabel = document.createElement('span');
        __prefixLabel.id = 'recurring-label';
        __prefixLabel.className = 'item';
        __prefixLabel.innerHTML = html;
        container.appendChild(__prefixLabel);
    },

    appendTimesLabel: (container, times) => {
        const html = document.getElementById('sylius-variants-recurring-times-label').innerHTML;
        const __recurringTimesLabel = document.createElement('span');
        __recurringTimesLabel.id = 'recurring-times';
        __recurringTimesLabel.className = 'item';

        const wrapper = document.createElement('div');
        wrapper.innerHTML = html;
        const firstChild = wrapper.firstElementChild;

        if (firstChild) {
            firstChild.insertAdjacentText('afterbegin', times + ' ');
            __recurringTimesLabel.appendChild(firstChild);
        } else {
            __recurringTimesLabel.innerHTML = html;
        }

        container.appendChild(__recurringTimesLabel);
    },

    addRecurringDetailsLabels: (itemContainer, totalContainer, interval, times) => {
        const __recurringContainer = document.createElement('div');

        model.appendRecurringLabel(__recurringContainer);
        model.appendTimesLabel(__recurringContainer, times);
        itemContainer.appendChild(__recurringContainer);

        model.appendIntervalLabel(totalContainer, interval);
    },

    clearLabels: () => {
        ['recurring-label', 'recurring-times', 'recurring-interval', 'every-label'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.remove();
        });
    }
};
