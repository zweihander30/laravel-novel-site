<?php

namespace App\Services\Comment;

use App\Models\Novel;
use App\Models\Comment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

class CommentService
{
    /**
     * コメント一覧を取得
     */
    public function getComments(int $novelId, int $perPage = 10): array
    {
        $novel = Novel::findOrFail($novelId);

        // ルートコメントとその返信を取得
        $comments = $novel->rootComments()->with('replies.replies.replies') // 3階層まで取得
            ->paginate($perPage);

        // 統計情報を計算
        $stats = $this->calculateStats($novel);

        return [
            'comments' => $comments,
            'stats' => $stats
        ];
    }

    /**
     * コメントを投稿
     */
    public function createComment(int $novelId, array $data): Comment
    {
        DB::beginTransaction();
        try {
            $novel = Novel::findOrFail($novelId);

            // 深さを計算
            $depth = 0;
            if (!empty($data['parent_id'])) {
                $parent = Comment::findOrFail($data['parent_id']);

                // 親コメントの検証
                $this->validateParentComment($parent, $novelId);

                $depth = $parent->depth + 1;

                // 深さ制限チェック
                if ($depth > 3) {
                    throw new \InvalidArgumentException('コメントのネストは3階層までです');
                }
            }
            $comment = Comment::create([
                'novel_id' => $novelId,
                'parent_id' => $data['parent_id'] ?? null,
                'user_name' => $data['user_name'],
                'user_identifier' => $data['user_identifier'],
                'content' => $data['content'],
                'depth' => $depth
            ]);

            DB::commit();
            Log::info('Comment created', ['comment_id' => $comment->id]);

            return $comment->load('replies');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create comment', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * コメントを削除
     */
    public function deleteComment(int $novelId, int $commentId, string $userIdentifier): array
    {
        DB::beginTransaction();
        try {
            $comment = Comment::where('novel_id', $novelId)
                ->where('id', $commentId)
                ->firstOrFail();

            // 所有者チェック
            if ($comment->user_identifier !== $userIdentifier) {
                throw new \UnauthorizedHttpException('このコメントを削除する権限がありません');
            }

            // 返信がある場合は内容を変更、ない場合は削除
            if ($comment->replies()->exists()) {
                $comment->update([
                    'content' => '[このコメントは削除されました]',
                    'user_name' => '[削除済み]'
                ]);
                $message = 'コメントの内容を削除しました';
            } else {
                $comment->delete();
                $message = 'コメントを削除しました';
            }

            DB::commit();
            Log::info('Comment deleted/modified', ['comment_id' => $commentId]);

            return ['message' => $message];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete comment', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * 人気のコメントを取得
     */
    public function getPopularComments(int $novelId, int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        return Comment::where('novel_id', $novelId)
            ->withCount('replies')
            ->orderBy('replies_count', 'desc')
            ->take($limit)
            ->get()
            ->filter(function ($comment) {
                return $comment->replies_count > 0;
            });
    }

    /**
     * 親コメントの検証
     */
    private function validateParentComment(Comment $parent, int $novelId): void
    {
        if ($parent->novel_id != $novelId) {
            throw new \InvalidArgumentException('親コメントが異なる小説のものです');
        }
    }

    /**
     * コメント統計を計算
     */
    private function calculateStats(Novel $novel): array
    {
        return [
            'total_comments' => $novel->comments()->count(),
            'unique_commenters' => $novel->comments()
                ->select('user_identifier')
                ->distinct()
                ->count(),
            'average_depth' => round($novel->comments()->avg('depth') ?? 0, 1)
        ];
    }
}
