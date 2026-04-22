<?php
/**
 * Banner Carousel Widget Template
 *
 * @var array<int, array<string, string>> $slides
 * @var string                            $carouselId
 */
?>
<?php if (empty($slides)): ?>
    <div class="w-full">
        <div class="flex h-48 items-center justify-center rounded-lg bg-base-200">
            <div class="text-center text-base-content/50">
                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto mb-2 h-12 w-12 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p class="text-sm font-medium"><?php esc_html_e('No banner yet', 'daisy-a-ripple-song'); ?></p>
                <p class="mt-1 text-xs"><?php esc_html_e('Please add banner content in the admin panel', 'daisy-a-ripple-song'); ?></p>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="w-full">
        <div class="relative">
            <div id="<?php echo esc_attr($carouselId); ?>" class="ars-banner-carousel-track flex w-full snap-x snap-mandatory gap-0 overflow-x-auto rounded-lg scroll-smooth">
                <?php foreach ($slides as $slide): ?>
                    <div class="min-w-full snap-center">
                        <?php if (!empty($slide['link'])): ?>
                            <a href="<?php echo esc_url($slide['link']); ?>" target="<?php echo esc_attr($slide['link_target']); ?>" class="block w-full" rel="<?php echo $slide['link_target'] === '_blank' ? 'noopener noreferrer' : ''; ?>">
                                <img src="<?php echo esc_url($slide['image']); ?>" class="h-48 w-full rounded-lg object-cover" alt="<?php echo esc_attr($slide['description']); ?>">
                            </a>
                        <?php else: ?>
                            <img src="<?php echo esc_url($slide['image']); ?>" class="h-48 w-full rounded-lg object-cover" alt="<?php echo esc_attr($slide['description']); ?>">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (count($slides) > 1): ?>
                <button type="button"
                        class="banner-prev absolute left-2 top-1/2 z-10 hidden h-8 w-8 -translate-y-1/2 items-center justify-center rounded-full bg-black/20 text-white backdrop-blur-sm transition-colors hover:bg-black/40 md:flex"
                        data-carousel-prev="<?php echo esc_attr($carouselId); ?>"
                        aria-label="<?php echo esc_attr__('Previous slide', 'daisy-a-ripple-song'); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m15 18-6-6 6-6" />
                    </svg>
                </button>

                <button type="button"
                        class="banner-next absolute right-2 top-1/2 z-10 hidden h-8 w-8 -translate-y-1/2 items-center justify-center rounded-full bg-black/20 text-white backdrop-blur-sm transition-colors hover:bg-black/40 md:flex"
                        data-carousel-next="<?php echo esc_attr($carouselId); ?>"
                        aria-label="<?php echo esc_attr__('Next slide', 'daisy-a-ripple-song'); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m9 18 6-6-6-6" />
                    </svg>
                </button>

                <div class="absolute bottom-3 left-0 right-0 z-10 flex justify-center gap-2">
                    <?php foreach ($slides as $index => $slide): ?>
                        <button type="button"
                                class="banner-dot <?php echo $index === 0 ? 'bg-white scale-125' : 'bg-white/50 hover:bg-white/80'; ?> h-2.5 w-2.5 rounded-full shadow-sm transition-all duration-300"
                                data-carousel="<?php echo esc_attr($carouselId); ?>"
                                data-index="<?php echo esc_attr((string) $index); ?>"
                                aria-label="<?php echo esc_attr(sprintf(__('Go to slide %d', 'daisy-a-ripple-song'), $index + 1)); ?>">
                        </button>
                    <?php endforeach; ?>
                </div>

                <script>
                    (function() {
                        const carouselId = <?php echo wp_json_encode($carouselId); ?>;
                        const carousel = document.getElementById(carouselId);

                        if (!carousel || carousel.dataset.widgetReady === '1') {
                            return;
                        }

                        carousel.dataset.widgetReady = '1';

                        const slides = Array.from(carousel.children);
                        const dots = Array.from(document.querySelectorAll('[data-carousel="' + carouselId + '"]'));
                        const previousButton = document.querySelector('[data-carousel-prev="' + carouselId + '"]');
                        const nextButton = document.querySelector('[data-carousel-next="' + carouselId + '"]');
                        let activeIndex = 0;
                        let autoplayTimer = null;

                        function slideWidth() {
                            return carousel.clientWidth || 1;
                        }

                        function updateDots(index) {
                            dots.forEach(function(dot, dotIndex) {
                                dot.classList.toggle('bg-white', dotIndex === index);
                                dot.classList.toggle('scale-125', dotIndex === index);
                                dot.classList.toggle('bg-white/50', dotIndex !== index);
                                dot.classList.toggle('hover:bg-white/80', dotIndex !== index);
                            });
                        }

                        function goTo(index) {
                            activeIndex = (index + slides.length) % slides.length;
                            carousel.scrollTo({
                                left: slideWidth() * activeIndex,
                                behavior: 'smooth'
                            });
                            updateDots(activeIndex);
                        }

                        function restartAutoplay() {
                            if (autoplayTimer) {
                                window.clearInterval(autoplayTimer);
                            }

                            autoplayTimer = window.setInterval(function() {
                                goTo(activeIndex + 1);
                            }, 5000);
                        }

                        if (previousButton) {
                            previousButton.addEventListener('click', function() {
                                goTo(activeIndex - 1);
                                restartAutoplay();
                            });
                        }

                        if (nextButton) {
                            nextButton.addEventListener('click', function() {
                                goTo(activeIndex + 1);
                                restartAutoplay();
                            });
                        }

                        dots.forEach(function(dot) {
                            dot.addEventListener('click', function() {
                                goTo(parseInt(dot.dataset.index || '0', 10));
                                restartAutoplay();
                            });
                        });

                        carousel.addEventListener('scroll', function() {
                            const nextIndex = Math.round(carousel.scrollLeft / slideWidth());
                            if (!Number.isNaN(nextIndex) && nextIndex !== activeIndex) {
                                activeIndex = Math.min(Math.max(nextIndex, 0), slides.length - 1);
                                updateDots(activeIndex);
                            }
                        }, { passive: true });

                        window.addEventListener('resize', function() {
                            carousel.scrollLeft = slideWidth() * activeIndex;
                        });

                        updateDots(activeIndex);
                        restartAutoplay();
                    })();
                </script>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
