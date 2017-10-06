@extends('templates.master')

@section('content')

@include('partials.archive-filters')

<div class="container main-container">
    @include('partials.breadcrumbs')

    <div class="grid">
        @if (get_field('archive_' . sanitize_title($postType) . '_show_sidebar_navigation', 'option'))
            @include('partials.sidebar-left')
        @endif

        <?php
            $cols = 'grid-md-12';
            if (is_active_sidebar('right-sidebar') && get_field('archive_' . sanitize_title($postType) . '_show_sidebar_navigation', 'option')) {
                $cols = 'grid-md-8 grid-lg-6';
            } elseif (is_active_sidebar('right-sidebar') || get_field('archive_' . sanitize_title($postType) . '_show_sidebar_navigation', 'option')) {
                $cols = 'grid-md-12 grid-lg-9';
            }
        ?>

        <div class="{{ $cols }}">

            @if (get_field('archive_' . sanitize_title($postType) . '_title', 'option') || is_category() || is_date())
            <div class="grid">
                <div class="grid-xs-12">
                    @if (get_field('archive_' . sanitize_title($postType) . '_title', 'option'))
                        @if (is_category())
                            <h1>{{ get_field('archive_' . sanitize_title($postType) . '_title', 'option') }}: {{ single_cat_title() }}</h1>
                        {!! category_description() !!}
                        @elseif (is_date())
                            <h1>{{ get_field('archive_' . sanitize_title($postType) . '_title', 'option') }}: {{ the_archive_title() }}</h1>
                        @else
                            <h1>{{ get_field('archive_' . sanitize_title($postType) . '_title', 'option') }}</h1>
                        @endif
                    @else
                        @if (is_category())
                            <h1>{{ single_cat_title() }}</h1>
                        {!! category_description() !!}
                        @elseif (is_date())
                            <h1>{{ the_archive_title() }}</h1>
                        @endif
                    @endif
                </div>
            </div>
            @endif

            @if (is_active_sidebar('content-area-top'))
                <div class="grid sidebar-content-area sidebar-content-area-top">
                    <?php dynamic_sidebar('content-area-top'); ?>
                </div>
            @endif

            <div class="grid">
                <?php $archiveObj = New \Municipio\Theme\EventArchive ?>
                @if (have_posts())
                    <?php $postNum = 0; ?>
                    <ul class="grid-md-12 event-archive">
                        @while(have_posts())
                            {!! the_post() !!}
                            <?php global $post; ?>
                            <?php echo $archiveObj->getRenderedItem(); ?>
                            <?php $postNum++; ?>
                        @endwhile
                    </ul>
                @else
                    <?php echo $archiveObj->getRenderedNoEvent(); ?>
                @endif
            </div>


            @if (is_active_sidebar('content-area'))
                <div class="grid sidebar-content-area sidebar-content-area-bottom">
                    <?php dynamic_sidebar('content-area'); ?>
                </div>
            @endif

            <div class="grid pagination">
                <div class="grid-sm-12 text-center type-list">
                    {!!
                        paginate_links(array(
                            'type' => 'list'
                        ))
                    !!}
                </div>
                <div class="grid-sm-12 text-center type-loadmore" style="display:none;">
                    <div class="o-button"><?php echo _x('Show more events', 'Event archive', 'municipio'); ?></div>
                </div>
            </div>
        </div>

        @include('partials.sidebar-right')
    </div>
</div>

@stop
