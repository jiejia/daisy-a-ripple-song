<?php

namespace Jiejia\DaisyARippleSong\Widgets;

use Jiejia\DaisyARippleSong\Abstracts\AbstractWidget;

/**
 * Authors Widget
 *
 * Display the authors list (members and guests) in the sidebar.
 * Members include administrators, editors, and authors.
 * Guests are contributors.
 */
class AuthorsWidget extends AbstractWidget
{

    /**
     * Return the WordPress widget ID.
     *
     * @return string
     */
    public function widgetId(): string
    {
        return 'authors_widget';
    }

    /**
     * Return the translated widget title.
     *
     * @return string
     */
    public function widgetTitle(): string
    {
        return __('aripplesong - Authors List', 'daisy-a-ripple-song');
    }

    /**
     * Return the translated widget description.
     *
     * @return string
     */
    public function widgetDescription(): string
    {
        return __('Display members and guest authors list', 'daisy-a-ripple-song');
    }

    /**
     * Front-end display of widget.
     *
     * @param array $args     Widget arguments from the sidebar registration.
     * @param array $instance Saved widget option values.
     * @return void
     */
    public function widget($args, $instance)
    {
        echo $args['before_widget'];

        /** @var string $membersTitle Section heading for site members. */
        $membersTitle = !empty($instance['members_title'])
            ? sanitize_text_field((string) $instance['members_title'])
            : __('Members', 'daisy-a-ripple-song');

        /** @var string $guestsTitle Section heading for guest contributors. */
        $guestsTitle = !empty($instance['guests_title'])
            ? sanitize_text_field((string) $instance['guests_title'])
            : __('Guests', 'daisy-a-ripple-song');

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
                $postCountsByUser    = count_many_users_posts($userIds, 'post', true);

                if (IS_PODCAST_PLUGIN_ACTIVATED) {
                    $episodeCountsByUser = count_many_users_posts($userIds, \Jiejia\ARippleSong\CPTs\Episode::slug(), true);
                }
            }
        }

        /** @var array<int, array<string, mixed>> $preparedMembers Prepared member cards. */
        $preparedMembers = $showMembers
            ? $this->prepareUsers($members, $postCountsByUser, $episodeCountsByUser)
            : [];

        /** @var array<int, array<string, mixed>> $preparedGuests Prepared guest cards. */
        $preparedGuests = $showGuests
            ? $this->prepareUsers($contributors, $postCountsByUser, $episodeCountsByUser)
            : [];

        echo $this->renderTemplate('authors', [
            'membersTitle' => $membersTitle,
            'guestsTitle' => $guestsTitle,
            'showMembers' => $showMembers,
            'showGuests' => $showGuests,
            'members' => $preparedMembers,
            'guests' => $preparedGuests,
        ]);

        echo $args['after_widget'];
    }

    /**
     * Back-end widget form displayed in the WordPress admin.
     *
     * @param array $instance Current widget settings.
     * @return void
     */
    public function form($instance)
    {
        /** @var string $membersTitle Current members section heading value. */
        $membersTitle = !empty($instance['members_title']) ? $instance['members_title'] : __('Members', 'daisy-a-ripple-song');

        /** @var string $guestsTitle Current guests section heading value. */
        $guestsTitle = !empty($instance['guests_title']) ? $instance['guests_title'] : __('Guests', 'daisy-a-ripple-song');

        /** @var bool $showMembers Current state of the show members toggle. */
        $showMembers = isset($instance['show_members']) ? (bool) $instance['show_members'] : true;

        /** @var bool $showGuests Current state of the show guests toggle. */
        $showGuests = isset($instance['show_guests']) ? (bool) $instance['show_guests'] : true;
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('members_title')); ?>">
                <?php esc_html_e('Members Title:', 'daisy-a-ripple-song'); ?>
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
                <?php esc_html_e('Show Members (Administrators, Editors, Authors)', 'daisy-a-ripple-song'); ?>
            </label>
        </p>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id('guests_title')); ?>">
                <?php esc_html_e('Guests Title:', 'daisy-a-ripple-song'); ?>
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
                <?php esc_html_e('Show Guests (Contributors)', 'daisy-a-ripple-song'); ?>
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
    public function update($newInstance, $oldInstance)
    {
        /** @var array<string, mixed> $instance Sanitized widget settings to persist. */
        $instance = [];

        $instance['members_title'] = !empty($newInstance['members_title'])
            ? sanitize_text_field((string) $newInstance['members_title'])
            : '';

        $instance['guests_title'] = !empty($newInstance['guests_title'])
            ? sanitize_text_field((string) $newInstance['guests_title'])
            : '';

        $instance['show_members'] = !empty($newInstance['show_members']) ? 1 : 0;
        $instance['show_guests'] = !empty($newInstance['show_guests']) ? 1 : 0;

        return $instance;
    }

    /**
     * Convert a list of users into widget card data.
     *
     * @param \WP_User[]        $users               The user list to prepare.
     * @param array<int, int>   $postCountsByUser    Pre-fetched standard post counts keyed by user ID.
     * @param array<int, int>   $episodeCountsByUser Pre-fetched episode counts keyed by user ID.
     * @return array<int, array<string, mixed>> Prepared card rows.
     */
    protected function prepareUsers(array $users, array $postCountsByUser, array $episodeCountsByUser): array
    {
        /** @var array<int, array<string, mixed>> $preparedUsers Prepared user rows. */
        $preparedUsers = [];

        foreach ($users as $user) {
            /** @var string $avatarUrl URL of the user's avatar image. */
            $avatarUrl = get_avatar_url($user->ID, ['size' => 192]);

            /** @var int $baseCount Sum of standard posts and podcast episodes authored by the user. */
            $baseCount = (int) ($postCountsByUser[$user->ID] ?? 0)
                + (int) ($episodeCountsByUser[$user->ID] ?? 0);

            /** @var int $participatedCount Number of podcast appearances linked through helper data. */
            $participatedCount = is_callable([\Jiejia\DaisyARippleSong\Supports\Helper::class, 'getParticipatedPodcastIds'])
                ? count(\Jiejia\DaisyARippleSong\Supports\Helper::getParticipatedPodcastIds($user->ID))
                : 0;

            /** @var int $postCount Total content count displayed next to the user name. */
            $postCount = $baseCount + $participatedCount;

            $preparedUsers[] = [
                'author_url' => get_author_posts_url($user->ID),
                'avatar_url' => $avatarUrl,
                'display_name' => $user->display_name,
                'post_count' => $postCount,
            ];
        }

        return $preparedUsers;
    }
}
