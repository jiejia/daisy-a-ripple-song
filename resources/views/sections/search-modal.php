<?php
/**
 * Search modal section template.
 */
?>
<dialog class="modal" id="search_modal">
    <div class="modal-box">
        <?php get_search_form(); ?>
    </div>

    <form method="dialog" class="modal-backdrop">
        <button type="submit"><?php esc_html_e('Close', 'daisy-a-ripple-song'); ?></button>
    </form>
</dialog>