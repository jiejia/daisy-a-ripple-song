<?php
/**
 * Footer Links Widget Template
 *
 * @var string                          $title
 * @var array<int, array<string, mixed>> $items
 */
?>
<?php if (!empty($items) || !empty($title)): ?>
    <div class="text-left bg-base-100/60 rounded-lg p-4">
        <?php if (!empty($title)): ?>
            <h4 class="text-base-content/70 text-lg font-bold mb-2"><?php echo esc_html($title); ?></h4>
        <?php endif; ?>

        <?php if (!empty($items)): ?>
            <ul class="grid grid-flow-row gap-2">
                <?php foreach ($items as $item): ?>
                    <li>
                        <?php if (!empty($item['url'])): ?>
                            <a href="<?php echo esc_url((string) $item['url']); ?>"
                               <?php echo !empty($item['new_tab']) ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
                                <?php echo esc_html((string) $item['text']); ?>
                            </a>
                        <?php else: ?>
                            <span><?php echo esc_html((string) $item['text']); ?></span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
<?php endif; ?>
