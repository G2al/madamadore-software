document.addEventListener('DOMContentLoaded', function() {
    // Trova tutti i DatePicker con l'attributo data-load-delivery-calendar
    const deliveryDatePickers = document.querySelectorAll('input[data-load-delivery-calendar]');

    deliveryDatePickers.forEach(function(input) {
        // Aspetta che Flatpickr sia inizializzato
        setTimeout(() => {
            if (input._flatpickr) {
                enhanceDeliveryDatePicker(input._flatpickr);
            }
        }, 500);
    });
});

function enhanceDeliveryDatePicker(flatpickr) {
    let deliveryData = {};

    // Funzione per caricare dati del calendario
    function loadDeliveryData() {
        const currentDressId = getCurrentDressId();
        const startDate = new Date();
        startDate.setMonth(startDate.getMonth() - 1);
        const endDate = new Date();
        endDate.setMonth(endDate.getMonth() + 3);

        const params = new URLSearchParams({
            start: startDate.toISOString().split('T')[0],
            end: endDate.toISOString().split('T')[0],
        });

        if (currentDressId) {
            params.append('exclude', currentDressId);
        }

        fetch(`/api/delivery-calendar?${params}`)
            .then(response => response.json())
            .then(data => {
                deliveryData = {};
                data.forEach(item => {
                    deliveryData[item.date] = item;
                });

                // Forza re-render del calendario
                flatpickr.redraw();
            })
            .catch(error => console.log('Delivery calendar data load failed:', error));
    }

    // Hook per colorare le date
    flatpickr.set('onDayCreate', function(dObj, dStr, fp, dayElem) {
        const dateStr = fp.formatDate(dayElem.dateObj, 'Y-m-d');
        const dayData = deliveryData[dateStr];

        if (dayData && dayData.count > 0) {
            // Rimuovi classi esistenti
            dayElem.classList.remove('delivery-light', 'delivery-medium', 'delivery-heavy', 'delivery-critical');

            // Aggiungi classe basata su intensità
            if (dayData.count <= 2) {
                dayElem.classList.add('delivery-light');
                dayElem.style.backgroundColor = '#fef3c7';
                dayElem.style.color = '#92400e';
            } else if (dayData.count <= 4) {
                dayElem.classList.add('delivery-medium');
                dayElem.style.backgroundColor = '#fed7aa';
                dayElem.style.color = '#9a3412';
            } else {
                dayElem.classList.add('delivery-critical');
                dayElem.style.backgroundColor = '#fecaca';
                dayElem.style.color = '#991b1b';
            }

            // Aggiungi tooltip
            dayElem.title = `${dayData.count} abiti - ${dayData.customers.join(', ')}`;
        }
    });

    // Hook per caricare nuovi dati quando si cambia mese
    flatpickr.set('onMonthChange', loadDeliveryData);
    flatpickr.set('onYearChange', loadDeliveryData);

    // Carica dati iniziali
    loadDeliveryData();
}

function getCurrentDressId() {
    // Cerca l'ID del dress dalla URL o da altri metodi
    const url = window.location.href;
    const match = url.match(/\/dresses\/(\d+)\/edit/);
    return match ? match[1] : null;
}