 <li>
    <div class="grid">
        <div class="grid-md-9">
        <h2><a href="{{ $href }}">{{ $title }}</a></h2>
            <div class="event-archive_meta">
                <p><small><i class="pricon pricon-calendar"></i>
                <strong>{{ $dateLang }}:</strong>
                    {{ $date }}
                </small></p>

                @if ($hasLocation)
                    <p><small><i class="pricon pricon-location-pin"></i>
                    <strong>{{ $locationLang }}:</strong> {{ $locationTitle }}
                    </small></p>
                @endif
            </div>

        @if ($postContentMode == 'custom' && $hasPostContent)
            <p>{!! $postContentTrim !!}</p>
        @else
            {!! $excerpt !!}
        @endif

        </div>
        @if ($thumbnailSource)
            <div class="grid-md-3">
                <img src="{{ $thumbnailSource }}">
            </div>
        @endif
    </div>
</li>