<?php
/**
 * Search modal section template.
 */
?>
<input type="checkbox" id="search-modal" class="modal-toggle" />
<div class="modal" role="dialog">
    <div class="modal-box">
        <?php get_search_form(); ?>
    </div>
    <label class="modal-backdrop" for="search-modal"><?php esc_html_e('Close', 'a-ripple-song'); ?></label>
</div>
