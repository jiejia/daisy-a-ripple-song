@php
    $tags = get_the_tags();
@endphp

@if($tags && !empty($tags))
<div class="grid grid-flow-row gap-2 mt-2">
    <ul class="flex flex-wrap gap-2">
        @foreach($tags as $tag)
        <li>
            <a href="{{ get_tag_link($tag->term_id) }}" class="text-xs text-base-content/50 bg-base-200/50 hover:bg-base-200 rounded-full py-0.5 px-2"># {{ $tag->name }}</a>
        </li>
        @endforeach
    </ul>
</div>
@endif
<?php
/**
 * Entry Meta Partial
 *
 * Expected args:
 * - post_id: Post ID used to render the meta line.
 *
 * @var array<string, mixed> $args
 */
?>
<p class="text-xs text-base-content/50">
    <time class="dt-published" datetime="<?php echo esc_attr(get_post_time('c', true, (int) ($args['post_id'] ?? 0))); ?>">
        <?php echo esc_html(get_the_date('', (int) ($args['post_id'] ?? 0))); ?>
    </time>
    <span class="ml-2">
        · <span><?php echo esc_html(number_format_i18n((int) get_post_meta((int) ($args['post_id'] ?? 0), '_views_count', true))); ?></span> <?php esc_html_e('views', 'a-ripple-song'); ?>
        <?php if (get_post_type((int) ($args['post_id'] ?? 0)) === (class_exists('A_Ripple_Song_Podcast_Episodes') ? \A_Ripple_Song_Podcast_Episodes::POST_TYPE : 'ars_episode')): ?>
            · <span><?php echo esc_html(number_format_i18n((int) get_post_meta((int) ($args['post_id'] ?? 0), '_play_count', true))); ?></span> <?php esc_html_e('plays', 'a-ripple-song'); ?>
        <?php endif; ?>
    </span>
</p>
