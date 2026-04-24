/**
 * Theme options admin runtime for the General and Social Links settings pages.
 */
(() => {
    /** @type {Record<string, any>} themeOptionsConfig Localized runtime data from WordPress. */
    const themeOptionsConfig = window.aripplesongThemeOptions || {};

    /** @type {Record<string, any>} logoConfig Logo crop configuration used by the media frame. */
    const logoConfig = themeOptionsConfig.logo || {};

    /** @type {Record<string, string>} i18nConfig Localized admin strings used by the runtime. */
    const i18nConfig = themeOptionsConfig.i18n || {};

    /** @type {number} logoWidth Target logo width in pixels. */
    const logoWidth = Number.parseInt(String(logoConfig.width || ''), 10) || 220;

    /** @type {number} logoHeight Target logo height in pixels. */
    const logoHeight = Number.parseInt(String(logoConfig.height || ''), 10) || 32;

    /** @type {string} siteLogoLabel Accessible logo label used by the preview image. */
    const siteLogoLabel = String(i18nConfig.siteLogo || 'Site Logo');

    /** @type {string} selectAndCropLabel Media modal action label. */
    const selectAndCropLabel = String(i18nConfig.selectAndCrop || 'Select and Crop');

    /** @type {string} selectSiteLogoLabel Media modal title label. */
    const selectSiteLogoLabel = String(i18nConfig.selectSiteLogo || 'Select Site Logo');

    /** @type {?Function} arsLogoCropper Cached cropper state constructor. */
    let arsLogoCropper = null;

    /**
     * Decide whether the selected attachment needs to be cropped.
     *
     * @param {number} imgWidth Original attachment width.
     * @param {number} imgHeight Original attachment height.
     * @returns {boolean}
     */
    function mustBeCropped(imgWidth, imgHeight) {
        if (imgWidth === logoWidth && imgHeight === logoHeight) {
            return false;
        }

        if (imgWidth <= logoWidth || imgHeight <= logoHeight) {
            return false;
        }

        return true;
    }

    /**
     * Build imgSelect options that lock the crop box to the logo aspect ratio.
     *
     * @param {Object} attachment Media library attachment model.
     * @param {Object} controller Cropper state controller.
     * @returns {Object}
     */
    function calculateImageSelectOptions(attachment, controller) {
        /** @type {number} realWidth Original image width. */
        const realWidth = Number.parseInt(String(attachment.get('width') || ''), 10) || 0;

        /** @type {number} realHeight Original image height. */
        const realHeight = Number.parseInt(String(attachment.get('height') || ''), 10) || 0;

        /** @type {number} ratio Locked crop ratio derived from the configured logo dimensions. */
        const ratio = logoWidth / logoHeight;

        /** @type {number} xInit Initial crop width. */
        let xInit = logoWidth;

        /** @type {number} yInit Initial crop height. */
        let yInit = logoHeight;

        if (realWidth / realHeight > ratio) {
            yInit = realHeight;
            xInit = yInit * ratio;
        } else {
            xInit = realWidth;
            yInit = xInit / ratio;
        }

        /** @type {number} x1 Crop box left offset. */
        const x1 = (realWidth - xInit) / 2;

        /** @type {number} y1 Crop box top offset. */
        const y1 = (realHeight - yInit) / 2;

        controller.set('canSkipCrop', !mustBeCropped(realWidth, realHeight));

        return {
            handles: true,
            keys: true,
            instance: true,
            persistent: true,
            imageWidth: realWidth,
            imageHeight: realHeight,
            minWidth: logoWidth > xInit ? xInit : logoWidth,
            minHeight: logoHeight > yInit ? yInit : logoHeight,
            x1,
            y1,
            x2: xInit + x1,
            y2: yInit + y1,
            aspectRatio: `${logoWidth}:${logoHeight}`,
        };
    }

    /**
     * Lazily build the cropper state constructor with a fixed output size.
     *
     * @returns {?Function}
     */
    function getLogoCropperCtor() {
        if (arsLogoCropper) {
            return arsLogoCropper;
        }

        if (!window.wp || !wp.media || !wp.media.controller || !wp.media.controller.Cropper) {
            return null;
        }

        arsLogoCropper = wp.media.controller.Cropper.extend({
            /**
             * Force the cropped output to match the required site logo dimensions.
             *
             * @param {Object} attachment Croppable media attachment.
             * @returns {*}
             */
            doCrop(attachment) {
                /** @type {Record<string, any>} cropDetails Crop details persisted by WordPress. */
                const cropDetails = attachment.get('cropDetails');

                cropDetails.dst_width = logoWidth;
                cropDetails.dst_height = logoHeight;
                attachment.set('cropDetails', cropDetails);

                return wp.ajax.post('crop-image', {
                    nonce: attachment.get('nonces').edit,
                    id: attachment.get('id'),
                    context: 'ars-site-logo',
                    cropDetails,
                });
            },
        });

        return arsLogoCropper;
    }

    /**
     * Render the selected logo preview inside the uploader container.
     *
     * @param {HTMLElement} preview Preview wrapper element.
     * @param {HTMLButtonElement} removeButton Remove button element.
     * @param {string} url Current logo URL.
     * @returns {void}
     */
    function renderLogoPreview(preview, removeButton, url) {
        preview.replaceChildren();

        if (url === '') {
            removeButton.disabled = true;
            return;
        }

        /** @type {HTMLImageElement} image Preview image element. */
        const image = document.createElement('img');

        image.className = 'ars-logo-preview__image';
        image.alt = siteLogoLabel;
        image.src = url;
        preview.appendChild(image);
        removeButton.disabled = false;
    }

    /**
     * Bind the native WordPress media uploader used by the site logo field.
     *
     * @returns {void}
     */
    function bindLogoUploader() {
        /** @type {HTMLElement | null} wrapper Main uploader wrapper. */
        const wrapper = document.querySelector('[data-ars-logo-uploader]');

        if (!wrapper || wrapper.dataset.ready === 'true') {
            return;
        }

        /** @type {HTMLInputElement | null} input Hidden URL input that stores the logo. */
        const input = wrapper.querySelector('[data-ars-logo-input]');

        /** @type {HTMLButtonElement | null} selectButton Button that opens the media frame. */
        const selectButton = wrapper.querySelector('[data-ars-logo-select]');

        /** @type {HTMLButtonElement | null} removeButton Button that clears the current logo. */
        const removeButton = wrapper.querySelector('[data-ars-logo-remove]');

        /** @type {HTMLElement | null} preview Preview wrapper that renders the selected logo. */
        const preview = wrapper.querySelector('[data-ars-logo-preview]');

        if (!input || !selectButton || !removeButton || !preview) {
            return;
        }

        wrapper.dataset.ready = 'true';

        /**
         * Synchronize the hidden field and preview after a media selection change.
         *
         * @param {string} url Selected logo URL.
         * @returns {void}
         */
        function syncValue(url) {
            input.value = url;
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
            renderLogoPreview(preview, removeButton, url);
        }

        selectButton.addEventListener('click', (event) => {
            event.preventDefault();

            /** @type {?Function} CropperCtor Cropper controller constructor. */
            const CropperCtor = getLogoCropperCtor();

            if (!CropperCtor || !window.wp || !wp.media || !wp.media.controller || !wp.media.controller.Library) {
                return;
            }

            /** @type {Object} cropperState Cropper state injected into the media workflow. */
            const cropperState = new CropperCtor({
                imgSelectOptions: calculateImageSelectOptions,
            });

            /** @type {Object} frame WordPress media frame used to select and crop the logo. */
            const frame = wp.media({
                button: {
                    text: selectAndCropLabel,
                    close: false,
                },
                states: [
                    new wp.media.controller.Library({
                        title: selectSiteLogoLabel,
                        library: wp.media.query({ type: 'image' }),
                        multiple: false,
                        date: false,
                        priority: 20,
                        suggestedWidth: logoWidth,
                        suggestedHeight: logoHeight,
                    }),
                    cropperState,
                ],
            });

            /**
             * Persist the selected image when WordPress allows skipping the crop step.
             *
             * @param {Object} selection Selected media attachment.
             * @returns {void}
             */
            function handleSkippedCrop(selection) {
                /** @type {string} url Selected attachment URL. */
                const url = String(selection.get('url') || '').trim();

                if (url !== '') {
                    syncValue(url);
                }
            }

            frame.on('select', () => {
                /** @type {Object | undefined} selection Selected media collection. */
                const selection = frame.state().get('selection');

                if (!selection) {
                    return;
                }

                /** @type {Object} attachment First selected attachment. */
                const attachment = selection.first();

                /** @type {string} mime Attachment MIME type. */
                const mime = String(attachment.get('mime') || '');

                /** @type {number} realWidth Selected image width. */
                const realWidth = Number.parseInt(String(attachment.get('width') || ''), 10) || 0;

                /** @type {number} realHeight Selected image height. */
                const realHeight = Number.parseInt(String(attachment.get('height') || ''), 10) || 0;

                if (mime === 'image/svg+xml' || realWidth === 0 || realHeight === 0) {
                    /** @type {string} url Selected attachment URL. */
                    const url = String(attachment.get('url') || '').trim();

                    if (url !== '') {
                        syncValue(url);
                    }

                    frame.close();
                    return;
                }

                frame.setState('cropper');
            });

            frame.on('cropped', (croppedImage) => {
                /** @type {string} url Cropped image URL returned by WordPress. */
                const url = String(croppedImage.url || '').trim();

                if (url !== '') {
                    syncValue(url);
                }
            });

            frame.on('skippedcrop', handleSkippedCrop);
            cropperState.on('skippedcrop', handleSkippedCrop);
            frame.open();
        });

        removeButton.addEventListener('click', (event) => {
            event.preventDefault();
            syncValue('');
        });

        renderLogoPreview(preview, removeButton, String(input.value || '').trim());
    }

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
        bindLogoUploader();
        bindThemePickers();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootstrapThemeOptions, { once: true });
    } else {
        bootstrapThemeOptions();
    }
})();
