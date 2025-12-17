@props(['title' => null, 'headerClass' => '', 'footer' => null])

<div class="card shadow-sm mb-4">
    @if($title)
        <div class="card-header {{ $headerClass }}">
            <h5 class="card-title mb-0">{{ $title }}</h5>
        </div>
    @endif
    <div class="card-body">
        {{ $slot }}
    </div>
    @if($footer)
        <div class="card-footer">
            {{ $footer }}
        </div>
    @endif
</div>









