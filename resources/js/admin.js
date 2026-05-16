/**
 * Theme options admin runtime.
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
            const select = findThemeSelect(target);

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
                    updateSelectValue(select, card.dataset.themeValue || '');
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                    syncCards();
                });
            });

            select.addEventListener('change', syncCards);
            syncCards();
        });
    }

    /**
     * Find the native select controlled by a theme picker.
     *
     * @param {string} target Theme mode target, such as light or dark.
     * @returns {HTMLSelectElement | null}
     */
    function findThemeSelect(target) {
        /** @type {HTMLSelectElement | null} select Field matched by explicit data attribute. */
        const select = document.querySelector(`select[data-theme-target="${target}"]`);

        if (select instanceof HTMLSelectElement) {
            return select;
        }

        /** @type {string} fieldSuffix Native field name suffix used as a fallback. */
        const fieldSuffix = target === 'dark' ? '_dark_theme' : '_light_theme';

        /** @type {HTMLSelectElement | null} compactSelect Legacy compact-input select. */
        const compactSelect = document.querySelector(`select[name$="${fieldSuffix}]"]`);

        if (compactSelect instanceof HTMLSelectElement) {
            return compactSelect;
        }

        return document.querySelector(`select[name$="${fieldSuffix}"]`);
    }

    /**
     * Update a native select value.
     *
     * @param {HTMLSelectElement} select Select element.
     * @param {string} value New selected theme value.
     * @returns {void}
     */
    function updateSelectValue(select, value) {
        select.value = value;
    }

    /**
     * Bootstrap all extracted theme options behaviors after the document is ready.
     *
     * @returns {void}
     */
    function bootstrapThemeOptions() {
        bindThemePickers();

        const observer = new MutationObserver(() => {
            bindThemePickers();
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true,
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootstrapThemeOptions, { once: true });
    } else {
        bootstrapThemeOptions();
    }
})();
