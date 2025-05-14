document.addEventListener('DOMContentLoaded', function () {
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
        if (draggable) {
            if (afterElement == null) {
                container.appendChild(draggable);
            } else {
                container.insertBefore(draggable, afterElement);
            }
        }
    });

    function getPaymentMethodPositions() {
        const draggables = [...container.querySelectorAll('.js-draggable')];
        return draggables.map((item, index) => {
            const { paymentMethod, paymentId } = item.dataset;
            return { id: index, name: paymentMethod, identifier: paymentId };
        });
    }

    function getDragAfterElement(container, y) {
        const draggableElements = [...container.querySelectorAll('.js-draggable:not(.dragging)')];

        return draggableElements.reduce(
            (closest, child) => {
                const box = child.getBoundingClientRect();
                const offset = y - box.top - box.height / 2;
                if (offset < 0 && offset > closest.offset) {
                    return { offset: offset, element: child };
                } else {
                    return closest;
                }
            },
            { offset: Number.NEGATIVE_INFINITY }
        ).element;
    }

    function changePositionAjaxAction(data) {
        const urlElement = document.getElementById('payment_methods');
        if (!urlElement) return;

        const url = urlElement.getAttribute('data-ajax-url');

        // Assuming the backend expects query parameters for GET
        const params = new URLSearchParams({ data: JSON.stringify(data) });

        fetch(`${url}?${params.toString()}`, {
            method: 'GET',
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                // Optionally handle success
            })
            .catch((error) => {
                // Optionally handle error
                console.error('AJAX error:', error);
            });
    }
});
