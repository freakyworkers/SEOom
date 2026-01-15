<div class="pinterest-masonry-item">
    <div class="card shadow-sm" style="overflow: hidden; border-radius: 12px;">
        <a href="{{ route('posts.show', ['site' => $site->slug, 'boardSlug' => $board->slug, 'post' => $post->id]) }}" 
           class="text-decoration-none text-dark">
            {{-- 이미지 영역 --}}
            @if($post->thumbnail_path)
                <div class="position-relative" style="overflow: hidden; background-color: #f8f9fa;">
                    <img src="{{ asset('storage/' . $post->thumbnail_path) }}" 
                         alt="{{ $post->title }}" 
                         class="img-fluid"
                         style="width: 100%; height: auto; display: block; object-fit: cover; min-height: 150px;">
                    {{-- 좋아요/댓글 수 표시 (우측 하단) --}}
                    <div class="position-absolute bottom-0 end-0 m-2 d-flex gap-2">
                        @if($board->enable_likes && $post->like_count > 0)
                            <span class="badge bg-dark bg-opacity-75">
                                <i class="bi bi-hand-thumbs-up"></i> {{ $post->like_count }}
                            </span>
                        @endif
                        @if($post->comments->count() > 0)
                            <span class="badge bg-dark bg-opacity-75">
                                <i class="bi bi-chat-dots"></i> {{ $post->comments->count() }}
                            </span>
                        @endif
                    </div>
                </div>
            @else
                @php
                    // 게시글 내용에서 첫 번째 이미지 추출
                    $content = $post->content;
                    preg_match('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $content, $matches);
                    $firstImage = $matches[1] ?? null;
                @endphp
                @if($firstImage)
                    <div class="position-relative" style="overflow: hidden;">
                        <img src="{{ $firstImage }}" 
                             alt="{{ $post->title }}" 
                             class="img-fluid"
                             style="width: 100%; height: auto; display: block;">
                        {{-- 좋아요/댓글 수 표시 (우측 하단) --}}
                        <div class="position-absolute bottom-0 end-0 m-2 d-flex gap-2">
                            @if($board->enable_likes && $post->like_count > 0)
                                <span class="badge bg-dark bg-opacity-75">
                                    <i class="bi bi-hand-thumbs-up"></i> {{ $post->like_count }}
                                </span>
                            @endif
                            @if($post->comments->count() > 0)
                                <span class="badge bg-dark bg-opacity-75">
                                    <i class="bi bi-chat-dots"></i> {{ $post->comments->count() }}
                                </span>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="position-relative bg-secondary bg-opacity-25 d-flex flex-column align-items-center justify-content-center" 
                         style="min-height: 150px;">
                        <i class="bi bi-image display-4 text-muted mb-2"></i>
                        <span class="text-muted small">No image</span>
                        {{-- 좋아요/댓글 수 표시 (우측 하단) --}}
                        <div class="position-absolute bottom-0 end-0 m-2 d-flex gap-2">
                            @if($board->enable_likes && $post->like_count > 0)
                                <span class="badge bg-dark bg-opacity-75">
                                    <i class="bi bi-hand-thumbs-up"></i> {{ $post->like_count }}
                                </span>
                            @endif
                            @if($post->comments->count() > 0)
                                <span class="badge bg-dark bg-opacity-75">
                                    <i class="bi bi-chat-dots"></i> {{ $post->comments->count() }}
                                </span>
                            @endif
                        </div>
                    </div>
                @endif
            @endif
            {{-- 핀터레스트 게시판 제목 표시 (pinterest_show_title이 true인 경우) --}}
            @if($board->pinterest_show_title ?? false)
                @php
                    $titleAlign = $board->pinterest_title_align ?? 'left';
                @endphp
                <div class="card-body p-2" style="background-color: rgba(255,255,255,0.95); text-align: {{ $titleAlign }};">
                    <h6 class="card-title mb-0 small text-truncate" style="font-size: 0.85rem; line-height: 1.3;">
                        {{ $post->title }}
                    </h6>
                </div>
            @endif
        </a>
    </div>
</div>
