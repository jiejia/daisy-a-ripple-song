<?php

namespace Jiejia\DaisyARippleSong\Widgets;

use Jiejia\DaisyARippleSong\Abstracts\AbstractWidget;

/**
 * Authors Widget.
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
     * Return all native field definitions for the widget form.
     *
     * @return array<int,array<string,mixed>>
     */
    public function fields(): array
    {
        return [
            [
                'type' => 'text',
                'key' => 'members_title',
                'label' => __('Members Title', 'daisy-a-ripple-song'),
                'placeholder' => __('Members', 'daisy-a-ripple-song'),
                'default' => (string) $this->defaultSettings()['members_title'],
            ],
            [
                'type' => 'checkbox',
                'key' => 'show_members',
                'label' => __('Show Members (Administrators, Editors, Authors)', 'daisy-a-ripple-song'),
                'default' => (bool) $this->defaultSettings()['show_members'],
            ],
            [
                'type' => 'text',
                'key' => 'guests_title',
                'label' => __('Guests Title', 'daisy-a-ripple-song'),
                'placeholder' => __('Guests', 'daisy-a-ripple-song'),
                'default' => (string) $this->defaultSettings()['guests_title'],
            ],
            [
                'type' => 'checkbox',
                'key' => 'show_guests',
                'label' => __('Show Guests (Contributors)', 'daisy-a-ripple-song'),
                'default' => (bool) $this->defaultSettings()['show_guests'],
            ],
        ];
    }

    /**
     * Return default values for the widget instance.
     *
     * @return array<string,mixed>
     */
    public function defaultSettings(): array
    {
        return [
            'members_title' => __('Members', 'daisy-a-ripple-song'),
            'guests_title' => __('Guests', 'daisy-a-ripple-song'),
            'show_members' => true,
            'show_guests' => true,
        ];
    }

    /**
     * Render the widget output.
     *
     * @param array $args Widget arguments from the sidebar registration.
     * @param array $instance Saved widget option values.
     * @return void
     */
    public function frontEnd($args, $instance): void
    {
        /** @var array<string,mixed> $widgetInstance Widget instance merged with defaults. */
        $widgetInstance = $this->mergeInstanceDefaults(is_array($instance) ? $instance : []);
        /** @var string $membersTitle Section heading for site members. */
        $membersTitle = $this->textValue($widgetInstance, 'members_title', __('Members', 'daisy-a-ripple-song'));
        /** @var string $guestsTitle Section heading for guest contributors. */
        $guestsTitle = $this->textValue($widgetInstance, 'guests_title', __('Guests', 'daisy-a-ripple-song'));
        /** @var bool $showMembers Whether to display the members section. */
        $showMembers = $this->boolValue($widgetInstance, 'show_members', true);
        /** @var bool $showGuests Whether to display the guests section. */
        $showGuests = $this->boolValue($widgetInstance, 'show_guests', true);

        /** @var \WP_User[] $members Site members. */
        $members = get_users([
            'role__in' => ['administrator', 'editor', 'author'],
            'orderby' => 'display_name',
            'order' => 'ASC',
        ]);

        /** @var \WP_User[] $contributors Guest contributors. */
        $contributors = get_users([
            'role' => 'contributor',
            'orderby' => 'display_name',
            'order' => 'ASC',
        ]);

        /** @var array<int,int> $postCountsByUser Standard post counts keyed by user ID. */
        $postCountsByUser = [];
        /** @var array<int,int> $episodeCountsByUser Podcast episode counts keyed by user ID. */
        $episodeCountsByUser = [];

        if (function_exists('count_many_users_posts')) {
            /** @var array<int,\WP_User> $allUsers Combined user list. */
            $allUsers = array_merge($members ?: [], $contributors ?: []);
            /** @var array<int,int> $userIds User IDs used by the count helpers. */
            $userIds = array_values(array_unique(array_map(static function (\WP_User $user): int {
                return (int) $user->ID;
            }, $allUsers)));

            if (!empty($userIds)) {
                $postCountsByUser = count_many_users_posts($userIds, 'post', true);

                if (IS_PODCAST_PLUGIN_ACTIVATED) {
                    $episodeCountsByUser = count_many_users_posts($userIds, \Jiejia\ARippleSong\CPTs\Episode::slug(), true);
                }
            }
        }

        echo $this->renderTemplate('authors', [
            'membersTitle' => $membersTitle,
            'guestsTitle' => $guestsTitle,
            'showMembers' => $showMembers,
            'showGuests' => $showGuests,
            'members' => $showMembers ? $this->prepareUsers($members, $postCountsByUser, $episodeCountsByUser) : [],
            'guests' => $showGuests ? $this->prepareUsers($contributors, $postCountsByUser, $episodeCountsByUser) : [],
        ]); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Convert a list of users into widget card data.
     *
     * @param \WP_User[] $users The user list to prepare.
     * @param array<int,int> $postCountsByUser Pre-fetched standard post counts keyed by user ID.
     * @param array<int,int> $episodeCountsByUser Pre-fetched episode counts keyed by user ID.
     * @return array<int,array<string,mixed>>
     */
    protected function prepareUsers(array $users, array $postCountsByUser, array $episodeCountsByUser): array
    {
        /** @var array<int,array<string,mixed>> $preparedUsers Prepared user rows. */
        $preparedUsers = [];

        foreach ($users as $user) {
            /** @var string $avatarUrl URL of the user's avatar image. */
            $avatarUrl = get_avatar_url($user->ID, ['size' => 192]);
            /** @var int $baseCount Sum of standard posts and podcast episodes authored by the user. */
            $baseCount = (int) ($postCountsByUser[$user->ID] ?? 0) + (int) ($episodeCountsByUser[$user->ID] ?? 0);
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
