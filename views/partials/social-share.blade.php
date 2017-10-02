<ul class="share share-social share-social-icon-md share-horizontal share-no-labels hidden-print inline-block">
    <li>
        <a class="share-social-facebook" data-action="share-popup" href="https://www.facebook.com/sharer/sharer.php?u={!! urlencode(wp_get_shortlink()) !!}" data-tooltip="<?php _e('Share on', 'municipio'); ?> Facebook">
            <i class="pricon pricon-facebook"></i>
            <span><?php _e('Share on', 'municipio'); ?> Facebook</span>
        </a>
    </li>
    <li>
        <a class="share-social-twitter" data-action="share-popup" href="http://twitter.com/share?url={!! urlencode(wp_get_shortlink()) !!}" data-tooltip="<?php _e('Share on', 'municipio'); ?> Twitter">
            <i class="pricon pricon-twitter"></i>
            <span><?php _e('Share on', 'municipio'); ?> Twitter</span>
        </a>
    </li>
    <li>
        <a class="share-social-linkedin" data-action="share-popup" href="https://www.linkedin.com/shareArticle?mini=true&amp;url={!! urlencode(wp_get_shortlink()) !!}&amp;title={{ urlencode(get_the_title()) }}" data-tooltip="<?php _e('Share on', 'municipio'); ?> LinkedIn">
            <i class="pricon pricon-linkedin"></i>
            <span><?php _e('Share on', 'municipio'); ?> LinkedIn</span>
        </a>
    </li>
</ul>
