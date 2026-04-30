/**
 * Theme options admin runtime for the General settings page.
 */
(() => {
    /**
     * Bind the custom DaisyUI theme picker cards to their hidden select fields.
     *
     * @returns {void}
     */
    function bindThemePickers() {
        document.querySelectorAll('[data-ars-theme-picker]').forEach((picker) => {
            if (!(picker instanceof HTMLElement) || picker.dataset.ready === 'true') {
                return;
            }

            /** @type {string} target Shared selector key used by the picker and native select. */
            const target = picker.dataset.themeTarget || '';

            /** @type {HTMLSelectElement | null} select Hidden native select field. */
            const select = document.querySelector(`select[data-theme-target="${target}"]`);

            /** @type {HTMLElement[]} cards Clickable theme cards inside the picker. */
            const cards = Array.from(picker.querySelectorAll('[data-theme-value]')).filter((card) => card instanceof HTMLElement);

            if (!select || cards.length === 0) {
                return;
            }

            picker.dataset.ready = 'true';

            /**
             * Reflect the selected theme value in the visible card state.
             *
             * @returns {void}
             */
            function syncCards() {
                cards.forEach((card) => {
                    card.classList.toggle('is-active', card.dataset.themeValue === select.value);
                });
            }

            cards.forEach((card) => {
                card.addEventListener('click', (event) => {
                    event.preventDefault();
                    select.value = card.dataset.themeValue || '';
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                    syncCards();
                });
            });

            select.addEventListener('change', syncCards);
            syncCards();
        });
    }

    /**
     * Bootstrap all extracted theme options behaviors after the document is ready.
     *
     * @returns {void}
     */
    function bootstrapThemeOptions() {
        bindThemePickers();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootstrapThemeOptions, { once: true });
    } else {
        bootstrapThemeOptions();
    }
})();
