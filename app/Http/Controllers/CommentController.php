<?php

namespace App\Http\Controllers;

use App\Services\CommentService;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    protected $commentService;

    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
        $this->middleware('auth');
    }

    /**
     * Store a newly created comment.
     */
    public function store(Request $request, Site $site, $boardSlug, Post $post)
    {
        // 권한 체크
        $board = $post->board;
        $permission = $board->comment_permission ?? 'user';
        if ($permission === 'user' && !auth()->check()) {
            return back()->with('error', '댓글 작성 권한이 없습니다. 로그인이 필요합니다.');
        }
        if ($permission === 'admin' && (!auth()->check() || !auth()->user()->canManage())) {
            return back()->with('error', '댓글 작성 권한이 없습니다.');
        }

        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $request->all();
        $data['post_id'] = $post->id;
        $this->commentService->create($data, auth()->id(), $site->id);

        return back()->with('success', '댓글이 작성되었습니다.');
    }

    /**
     * Show the form for editing the specified comment.
     */
    public function edit(Site $site, $boardSlug, Post $post, Comment $comment)
    {
        $this->authorize('update', $comment);

        return view('comments.edit', compact('comment', 'post', 'site', 'boardSlug'));
    }

    /**
     * Update the specified comment.
     */
    public function update(Request $request, Site $site, $boardSlug, Post $post, Comment $comment)
    {
        $this->authorize('update', $comment);

        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $this->commentService->update($comment, $request->all());

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => '댓글이 수정되었습니다.',
                'content' => $comment->content
            ]);
        }

        return redirect()->route('posts.show', [
            'site' => $site->slug,
            'boardSlug' => $boardSlug,
            'post' => $post->id
        ])->with('success', '댓글이 수정되었습니다.');
    }

    /**
     * Remove the specified comment.
     */
    public function destroy(Site $site, $boardSlug, Post $post, Comment $comment)
    {
        $this->authorize('delete', $comment);

        $this->commentService->delete($comment);

        return back()->with('success', '댓글이 삭제되었습니다.');
    }

    /**
     * Adopt a comment (작성자 또는 운영자 채택)
     */
    public function adopt(Request $request, Site $site, $boardSlug, Post $post, Comment $comment)
    {
        $board = $post->board;
        $type = $request->input('type', 'author'); // 'author' or 'admin'
        
        // 권한 체크
        if ($type === 'author') {
            // 작성자 채택
            if (!auth()->check() || auth()->id() !== $post->user_id) {
                return response()->json(['message' => '작성자만 댓글을 채택할 수 있습니다.'], 403);
            }
            if (!$board->enable_author_comment_adopt) {
                return response()->json(['message' => '작성자 댓글 채택 기능이 비활성화되어 있습니다.'], 403);
            }
            if ($post->adopted_comment_id) {
                return response()->json(['message' => '이미 채택된 댓글이 있습니다.'], 400);
            }
            if (!$post->adoption_points || $post->adoption_points <= 0) {
                return response()->json(['message' => '채택 포인트가 설정되지 않았습니다.'], 400);
            }
            
            // 포인트 지급 (간단한 구현 - 실제 포인트 시스템이 완성되면 수정 필요)
            $adoptionPoints = $post->adoption_points;
            
        } else if ($type === 'admin') {
            // 운영자 채택
            if (!auth()->check() || !auth()->user()->canManage()) {
                return response()->json(['message' => '운영자만 댓글을 채택할 수 있습니다.'], 403);
            }
            if (!$board->enable_admin_comment_adopt) {
                return response()->json(['message' => '운영자 댓글 채택 기능이 비활성화되어 있습니다.'], 403);
            }
            
            $adoptionPoints = (int) $request->input('points', 0);
            if ($adoptionPoints < 0) {
                return response()->json(['message' => '올바른 포인트를 입력하세요.'], 400);
            }
        } else {
            return response()->json(['message' => '잘못된 요청입니다.'], 400);
        }
        
        // 댓글 채택 처리
        $comment->update([
            'is_adopted' => true,
            'adoption_points' => $adoptionPoints
        ]);
        
        if ($type === 'author') {
            $post->update([
                'adopted_comment_id' => $comment->id
            ]);
        }
        
        // 포인트 지급/차감 처리
        if ($adoptionPoints > 0) {
            // 댓글 작성자에게 포인트 지급
            $comment->user->addPoints($adoptionPoints);
            
            // 작성자 채택인 경우 게시글 작성자에게서 포인트 차감
            if ($type === 'author') {
                $post->user->subtractPoints($adoptionPoints);
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => '댓글이 채택되었습니다.'
        ]);
    }

    /**
     * Report a comment (API).
     */
    public function reportComment(Site $site, $boardSlug, Post $post, Comment $comment, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first('reason') ?: '신고 사유를 입력해주세요.',
                'errors' => $validator->errors()
            ], 422);
        }

        // Comment가 해당 site와 post에 속하는지 확인
        if ($comment->site_id !== $site->id || $comment->post_id !== $post->id) {
            return response()->json([
                'success' => false,
                'error' => '댓글을 찾을 수 없습니다.'
            ], 404);
        }
        
        // Post가 해당 site에 속하는지 확인
        if ($post->site_id !== $site->id) {
            return response()->json([
                'success' => false,
                'error' => '게시글을 찾을 수 없습니다.'
            ], 404);
        }

        // Get reporter info
        $reporterId = Auth::id();
        $reporterGuestSessionId = null;
        $reporterNickname = null;
        
        if ($reporterId) {
            $reporterNickname = Auth::user()->nickname ?? Auth::user()->name;
        } else {
            return response()->json(['error' => '로그인이 필요합니다.'], 403);
        }

        // Get reported user info
        $reportedUserId = $comment->user_id;
        $reportedNickname = $comment->user ? ($comment->user->nickname ?? $comment->user->name) : '알 수 없음';

        // Create report
        $report = \App\Models\Report::create([
            'site_id' => $site->id,
            'reporter_id' => $reporterId,
            'reporter_guest_session_id' => $reporterGuestSessionId,
            'reporter_nickname' => $reporterNickname,
            'reported_user_id' => $reportedUserId,
            'reported_nickname' => $reportedNickname,
            'report_type' => 'comment',
            'comment_id' => $comment->id,
            'post_id' => $post->id, // 게시글 정보도 함께 저장
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => '신고가 접수되었습니다.',
        ]);
    }
}
