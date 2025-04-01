$ = window.$;
$(function () {
    const container = document.querySelector('.js-sortable');

    if (!container) {
        return;
    }

    const draggables = document.querySelectorAll('.js-draggable');

    draggables.forEach((draggable) => {
        draggable.addEventListener('dragstart', () => {
            draggable.classList.add('dragging');
        });

        draggable.addEventListener('dragend', () => {
            draggable.classList.remove('dragging');
            const payload = getPaymentMethodPositions();
            changePositionAjaxAction(payload);
        });
    });

    container.addEventListener('dragover', (event) => {
        event.preventDefault();
        const afterElement = getDragAfterElement(container, event.clientY);
        const draggable = document.querySelector('.dragging');
        if (afterElement == null) {
            container.appendChild(draggable);
        } else {
            container.insertBefore(draggable, afterElement);
        }
    });

    function getPaymentMethodPositions() {
        const draggables = [...container.querySelectorAll('.js-draggable')];
        const updatedPositions = [];

        draggables.map((item, index) => {
            const {paymentMethod, paymentId} = item.dataset;
            updatedPositions.push({id: index, name: paymentMethod, identifier: paymentId});
        });

        return updatedPositions;
    }

    function getDragAfterElement(container, y) {
        const draggableElements = [...container.querySelectorAll('.js-draggable:not(.dragging)')];

        return draggableElements.reduce(
            (closest, child) => {
                const box = child.getBoundingClientRect();
                const offset = y - box.top - box.height / 2;
                if (offset < 0 && offset > closest.offset) {
                    return {offset: offset, element: child};
                } else {
                    return closest;
                }
            },
            {offset: Number.NEGATIVE_INFINITY}
        ).element;
    }

    function changePositionAjaxAction(data) {
        const url = document.getElementById('payment_methods').getAttribute('data-ajax-url');

        $.ajax({
            type: 'GET',
            url: url,
            data: {data: data},
            success: function (data) {},
            error: function () {},
        });
    }
});
