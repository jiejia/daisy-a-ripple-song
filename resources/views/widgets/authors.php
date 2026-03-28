<div>
    <?php if ($showMembers && !empty($members)): ?>
        <h2 class="wp-block-heading"><?php echo esc_html($membersTitle); ?></h2>
        <div class="mt-4 grid grid-flow-row gap-2">
            <?php foreach ($members as $member): ?>
                <a href="<?php echo esc_url((string) $member['author_url']); ?>"
                   class="grid grid-cols-[40px_1fr_40px] items-center gap-2 rounded-lg bg-base-200/50 p-2 hover:bg-base-200">
                    <div class="avatar">
                        <div class="ring-base-content/50 ring-offset-base-100 w-6 rounded-full ring-1 ring-offset-1">
                            <img src="<?php echo esc_url((string) $member['avatar_url']); ?>"
                                 alt="<?php echo esc_attr((string) $member['display_name']); ?>">
                        </div>
                    </div>
                    <span class="text-xs"><?php echo esc_html((string) $member['display_name']); ?></span>
                    <span class="text-xs text-base-content/50"><?php echo esc_html((string) $member['post_count']); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($showGuests && !empty($guests)): ?>
        <h2 class="wp-block-heading <?php echo ($showMembers && !empty($members)) ? 'mt-4' : ''; ?>">
            <?php echo esc_html($guestsTitle); ?>
        </h2>
        <div class="mt-4 grid grid-flow-row gap-2">
            <?php foreach ($guests as $guest): ?>
                <a href="<?php echo esc_url((string) $guest['author_url']); ?>"
                   class="grid grid-cols-[40px_1fr_40px] items-center gap-2 rounded-lg bg-base-200/50 p-2 hover:bg-base-200">
                    <div class="avatar">
                        <div class="ring-base-content/50 ring-offset-base-100 w-6 rounded-full ring-1 ring-offset-1">
                            <img src="<?php echo esc_url((string) $guest['avatar_url']); ?>"
                                 alt="<?php echo esc_attr((string) $guest['display_name']); ?>">
                        </div>
                    </div>
                    <span class="text-xs"><?php echo esc_html((string) $guest['display_name']); ?></span>
                    <span class="text-xs text-base-content/50"><?php echo esc_html((string) $guest['post_count']); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (empty($members) && empty($guests)): ?>
        <div class="py-8 text-center">
            <div class="text-base-content/50">
                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto mb-2 h-12 w-12 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <p class="text-sm font-medium"><?php esc_html_e('No authors yet', 'a-ripple-song'); ?></p>
                <p class="mt-1 text-xs"><?php esc_html_e('Authors will appear here after adding users', 'a-ripple-song'); ?></p>
            </div>
        </div>
    <?php endif; ?>
</div>
