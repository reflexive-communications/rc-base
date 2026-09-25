(function () {
    'use strict';

    /**
     * Remove a closed Advanced Search criteria pane from the submitted form.
     *
     * @param {MouseEvent} event
     */
    function closeCriteriaPane(event) {
        if (!(event.target instanceof Element)) {
            return;
        }

        const closeButton = event.target.closest('.crm-ajax-accordion .crm-close-accordion');
        const summary = event.target.closest('.crm-ajax-accordion > summary');
        if (!closeButton && summary) {
            const form = summary.closest('form');
            const markerName = `hidden_${summary.id}`;
            form?.querySelectorAll('[data-rc-base-closed-pane]').forEach((marker) => {
                if (marker.name === markerName) {
                    marker.remove();
                }
            });
        }
        if (!closeButton) {
            return;
        }

        const pane = closeButton.closest('.crm-ajax-accordion');
        if (!pane) {
            return;
        }

        const form = pane.closest('form');
        if (!form || !summary) {
            return;
        }

        const markerName = `hidden_${summary.id}`;
        let paneMarker = Array.from(pane.querySelectorAll('input[name]')).find(
            (input) => input.name === markerName,
        );
        if (!paneMarker) {
            paneMarker = document.createElement('input');
            paneMarker.type = 'hidden';
            paneMarker.name = markerName;
        }

        paneMarker.value = '0';
        paneMarker.dataset.rcBaseClosedPane = 'true';

        pane.querySelectorAll('.crm-accordion-body [name]').forEach((field) => {
            if (field !== paneMarker) {
                field.disabled = true;
            }
        });
        form.append(paneMarker);

        window.setTimeout(() => {
            pane.removeAttribute('open');
            pane.classList.add('collapsed');
        });
    }

    document.addEventListener('click', closeCriteriaPane, true);
})();
