 <li>
    <div class="grid">
        <div class="grid-md-9">
        <h2><a href="{{ esc_url(add_query_arg('date', preg_replace('/\D/', '', $post->start_date), the_permalink())) }}">{{ the_title() }}</a></h2>
            <div class="event-archive_meta">
                <p><small><i class="pricon pricon-calendar"></i>
                <strong><?php _ex('Date', 'Event archive', 'municipio'); ?>:</strong>
                    {{ \Municipio\Helper\Event::formatEventDate($post->start_date, $post->end_date) }}
                </small></p>

                <?php $location = get_field('location'); ?>
                @if (!empty($location['title']))
                    <p><small><i class="pricon pricon-location-pin"></i>
                    <strong><?php _ex('Location', 'Event archive','municipio'); ?>:</strong> {{ $location['title'] }}
                    </small></p>
                @endif
            </div>

        @if ($post->content_mode == 'custom' && ! empty($post->content))
            <p>{{ wp_trim_words($post->content, 50, ' [...]') }}</p>
        @else
            {{ the_excerpt() }}
        @endif

        </div>
        @if (municipio_get_thumbnail_source(null,array(400,250)))
            <div class="grid-md-3">
                <img src="{{ municipio_get_thumbnail_source(null,array(400,250)) }}">
            </div>
        @endif
    </div>
</li>