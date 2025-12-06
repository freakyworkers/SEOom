@props(['title', 'subtitle' => null, 'actions' => null])

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">{{ $title }}</h2>
        @if($subtitle)
            <p class="text-muted mb-0">{{ $subtitle }}</p>
        @endif
    </div>
    @if($actions)
        <div>
            {!! $actions !!}
        </div>
    @endif
</div>



