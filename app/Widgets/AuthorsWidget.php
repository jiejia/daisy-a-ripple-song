<?php

namespace App\Widgets;

/**
 * Authors Widget
 *
 * Display the authors list (members and guests) in the sidebar.
 * Members include administrators, editors, and authors.
 * Guests are contributors.
 */
class AuthorsWidget extends \WP_Widget {

    /**
     * Register widget with WordPress.
     */
    public function __construct() {
        parent::__construct(
            'authors_widget',
            __('aripplesong - Authors List', 'a-ripple-song'),
            ['description' => __('Display members and guest authors list', 'a-ripple-song')]
        );
    }

    /**
     * Front-end display of widget.
     *
     * @param array $args     Widget arguments from the sidebar registration.
     * @param array $instance Saved widget option values.
     * @return void
     */
    public function widget($args, $instance) {
        echo $args['before_widget'];

        /** @var string $membersTitle Section heading for site members. */
        $membersTitle = !empty($instance['members_title']) ? $instance['members_title'] : __('Members', 'a-ripple-song');

        /** @var string $guestsTitle Section heading for guest contributors. */
        $guestsTitle = !empty($instance['guests_title']) ? $instance['guests_title'] : __('Guests', 'a-ripple-song');

        /** @var bool $showMembers Whether to display the members section. */
        $showMembers = isset($instance['show_members']) ? (bool) $instance['show_members'] : true;

        /** @var bool $showGuests Whether to display the guests section. */
        $showGuests = isset($instance['show_guests']) ? (bool) $instance['show_guests'] : true;

        /** @var \WP_User[] $members Site members (administrators, editors, authors). */
        $members = get_users([
            'role__in' => ['administrator', 'editor', 'author'],
            'orderby'  => 'display_name',
            'order'    => 'ASC',
        ]);

        /** @var \WP_User[] $contributors Guest contributors. */
        $contributors = get_users([
            'role'    => 'contributor',
            'orderby' => 'display_name',
            'order'   => 'ASC',
        ]);

        /**
         * Precompute post counts to avoid repeated queries inside the loops.
         *
         * @var array<int, int> $postCountsByUser   Standard post counts keyed by user ID.
         * @var array<int, int> $episodeCountsByUser Podcast episode counts keyed by user ID.
         */
        $postCountsByUser    = [];
        $episodeCountsByUser = [];

        if (function_exists('count_many_users_posts')) {
            $allUsers = array_merge($members ?: [], $contributors ?: []);
            $userIds  = array_values(array_unique(array_map(static function (\WP_User $user): int {
                return (int) $user->ID;
            }, $allUsers)));

            if (!empty($userIds)) {
                /** @var string $episodePostType The custom podcast episode post type slug. */
                $episodePostType = class_exists('A_Ripple_Song_Podcast_Episodes')
                    ? \A_Ripple_Song_Podcast_Episodes::POST_TYPE
                    : 'ars_episode';

                $postCountsByUser    = count_many_users_posts($userIds, 'post', true);
                $episodeCountsByUser = count_many_users_posts($userIds, $episodePostType, true);
            }
        }

        ?>
        <div class="">

            <?php if ($showMembers && !empty($members)): ?>
            <h2 class="wp-block-heading"><?php echo esc_html($membersTitle); ?></h2>
            <div class="grid grid-flow-row gap-2 mt-4">
                <?php foreach ($members as $user): ?>
                    <?php echo $this->renderUserRow($user, $postCountsByUser, $episodeCountsByUser); ?>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if ($showGuests && !empty($contributors)): ?>
            <h2 class="wp-block-heading <?php echo ($showMembers && !empty($members)) ? 'mt-4' : ''; ?>">
                <?php echo esc_html($guestsTitle); ?>
            </h2>
            <div class="grid grid-flow-row gap-2 mt-4">
                <?php foreach ($contributors as $user): ?>
                    <?php echo $this->renderUserRow($user, $postCountsByUser, $episodeCountsByUser); ?>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if ((!$showMembers || empty($members)) && (!$showGuests || empty($contributors))): ?>
            <div class="text-center py-8">
                <div class="text-base-content/50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-2 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <p class="text-sm font-medium"><?php esc_html_e('No authors yet', 'a-ripple-song'); ?></p>
                    <p class="text-xs mt-1"><?php esc_html_e('Authors will appear here after adding users', 'a-ripple-song'); ?></p>
                </div>
            </div>
            <?php endif; ?>

        </div>
        <?php

        echo $args['after_widget'];
    }

    /**
     * Render a single user row with avatar, name, and post count.
     *
     * @param \WP_User          $user                The user object to render.
     * @param array<int, int>   $postCountsByUser    Pre-fetched standard post counts keyed by user ID.
     * @param array<int, int>   $episodeCountsByUser Pre-fetched episode counts keyed by user ID.
     * @return string HTML markup for the user row.
     */
    protected function renderUserRow(\WP_User $user, array $postCountsByUser, array $episodeCountsByUser): string {
        /** @var string $avatarUrl URL of the user's avatar image. */
        $avatarUrl = get_avatar_url($user->ID, ['size' => 192]);

        /** @var int $baseCount Sum of standard posts and podcast episodes authored by the user. */
        $baseCount = (int) ($postCountsByUser[$user->ID] ?? 0)
                   + (int) ($episodeCountsByUser[$user->ID] ?? 0);

        /**
         * Count episodes where the user participated but is not the primary author.
         * Falls back to 0 if the helper function is not available.
         *
         * @var int $participatedCount Number of episodes the user participated in.
         */
        $participatedCount = function_exists('\App\Helper::getParticipatedPodcastIds')
            ? count(\App\Helper::getParticipatedPodcastIds($user->ID))
            : 0;

        /** @var int $postCount Total content count displayed next to the user name. */
        $postCount = $baseCount + $participatedCount;

        ob_start();
        ?>
        <a href="<?php echo esc_url(get_author_posts_url($user->ID)); ?>"
           class="grid grid-cols-[40px_1fr_40px] items-center gap-2 bg-base-200/50 hover:bg-base-200 rounded-lg p-2">
            <div class="avatar">
                <div class="ring-base-content/50 ring-offset-base-100 w-6 rounded-full ring-1 ring-offset-1">
                    <img src="<?php echo esc_url($avatarUrl); ?>"
                         alt="<?php echo esc_attr($user->display_name); ?>" />
                </div>
            </div>
            <span class="text-xs"><?php echo esc_html($user->display_name); ?></span>
            <span class="text-xs text-base-content/50"><?php echo esc_html($postCount); ?></span>
        </a>
        <?php
        return ob_get_clean();
    }

    /**
     * Back-end widget form displayed in the WordPress admin.
     *
     * @param array $instance Current widget settings.
     * @return void
     */
    public function form($instance) {
        /** @var string $membersTitle Current members section heading value. */
        $membersTitle = !empty($instance['members_title']) ? $instance['members_title'] : __('Members', 'a-ripple-song');

        /** @var string $guestsTitle Current guests section heading value. */
        $guestsTitle = !empty($instance['guests_title']) ? $instance['guests_title'] : __('Guests', 'a-ripple-song');

        /** @var bool $showMembers Current state of the show members toggle. */
        $showMembers = isset($instance['show_members']) ? $instance['show_members'] : true;

        /** @var bool $showGuests Current state of the show guests toggle. */
        $showGuests = isset($instance['show_guests']) ? $instance['show_guests'] : true;
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('members_title')); ?>">
                <?php esc_html_e('Members Title:', 'a-ripple-song'); ?>
            </label>
            <input class="widefat"
                   id="<?php echo esc_attr($this->get_field_id('members_title')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('members_title')); ?>"
                   type="text"
                   value="<?php echo esc_attr($membersTitle); ?>">
        </p>

        <p>
            <input class="checkbox"
                   type="checkbox"
                   <?php checked($showMembers); ?>
                   id="<?php echo esc_attr($this->get_field_id('show_members')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('show_members')); ?>">
            <label for="<?php echo esc_attr($this->get_field_id('show_members')); ?>">
                <?php esc_html_e('Show Members (Administrators, Editors, Authors)', 'a-ripple-song'); ?>
            </label>
        </p>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id('guests_title')); ?>">
                <?php esc_html_e('Guests Title:', 'a-ripple-song'); ?>
            </label>
            <input class="widefat"
                   id="<?php echo esc_attr($this->get_field_id('guests_title')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('guests_title')); ?>"
                   type="text"
                   value="<?php echo esc_attr($guestsTitle); ?>">
        </p>

        <p>
            <input class="checkbox"
                   type="checkbox"
                   <?php checked($showGuests); ?>
                   id="<?php echo esc_attr($this->get_field_id('show_guests')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('show_guests')); ?>">
            <label for="<?php echo esc_attr($this->get_field_id('show_guests')); ?>">
                <?php esc_html_e('Show Guests (Contributors)', 'a-ripple-song'); ?>
            </label>
        </p>
        <?php
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @param array $newInstance New widget settings submitted from the form.
     * @param array $oldInstance Previous widget settings.
     * @return array Sanitized settings to be saved.
     */
    public function update($newInstance, $oldInstance) {
        /** @var array $instance Sanitized widget settings to persist. */
        $instance = [];

        $instance['members_title'] = !empty($newInstance['members_title'])
            ? sanitize_text_field($newInstance['members_title'])
            : 'Members';

        $instance['guests_title'] = !empty($newInstance['guests_title'])
            ? sanitize_text_field($newInstance['guests_title'])
            : 'Guests';

        $instance['show_members'] = !empty($newInstance['show_members']) ? 1 : 0;
        $instance['show_guests']  = !empty($newInstance['show_guests']) ? 1 : 0;

        return $instance;
    }
}
