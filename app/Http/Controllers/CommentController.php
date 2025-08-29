<?php

namespace App\Http\Controllers;

use App\Models\Novel;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommentController extends Controller
{
    //　コメント一覧取得（ページネーション付き）
    public function index($novelId)
    {
        $novel = Novel::findOrFail($novelId);

        // ルートコメントとその返信を取得.
        $comments = $novel->rootComments()
            ->with('replies.replies.replies') // 3階層まで取得
            ->paginate(10);

        // コメント統計
        $stats = [
            'total_comments' => $novel->comments()->count(),
            'unique_commenters' => $novel->comments()->distinct('user_identifier')->count('user_identifier'),
            'average_depth' => $novel->comments()->avg('depth') ?? 0
        ];

        return response()->json([
            'comments' => $comments,
            'stats' => $stats
        ]);
    }

    // コメント投稿
    public function store(Request $request, $novelId)
    {

        Log::info('Comment store called', ['novel_id' => $novelId, 'request' => $request->all()]);

        $request->validate([
            'user_name' => 'required|string|max:50',
            'user_identifier' => 'required|string',
            'content' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:comments,id'
        ]);

        $novel = Novel::findOrFail($novelId);

        // 親コメントがある場合、深さを計算
        $depth = 0;
        if ($request->parent_id) {
            $parent = Comment::findOrFail($request->parent_id);

            // 同じ小説のコメントか確認
            if ($parent->novel_id != $novelId) {
                return response()->json(['error' => 'Invalid parent comment'], 400);
            }

            $depth = $parent->depth + 1;

            // 深さ制限（最大3階層）
            if ($depth > 3) {
                return response()->json(['error' => 'Maximum nesting depth reached'], 404);
            }
        }
        $comment = Comment::create([
            'novel_id' => $novelId,
            'parent_id' => $request->parent_id,
            'user_name' => $request->user_name,
            'user_identifier' => $request->user_identifier,
            'content' => $request->content,
            'depth' => $depth
        ]);

        return response()->json($comment->load('replies'), 201);
    }

    // コメント削除（自分のコメントのみ）
    public function destroy(Requset $request, $novelId, $commentId)
    {
        $comment = Comment::where('novel_id', $novelId)
            ->where('id', $commentId)
            ->firstOrFail();

        // 本人確認
        if ($comment->user_identifier !== $request->input('user_identifier')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // 返信がある場合は内容を「削除されました」に変更
        if ($comment->replies()->exists()) {
            $comment->update([
                'content' => '[このコメントは削除されました]',
                'user_name' => '[削除済み]'
            ]);
            return response()->json(['message' => 'Commentt content removed']);
        } else {
            // 返信がない場合は完全に削除.
            $comment->delete();
            return response()->json(['message' => 'Comment deleted']);
        }
    }

    // 人気のコメント取得（返信が多い順）
    public function popular($novelId)
    {
        $novel = Novel::findOrFail($novelId);

        $popularComments = Comment::where('novel_id', $novelId)
            ->withCount('replies')
            ->orderBy('replies_count', 'desc')
            ->take(5)
            ->get()
            ->filter(function ($comment) {
                return $comment->replies_count > 0;
            });

        return response()->json($popularComments);
    }
}
