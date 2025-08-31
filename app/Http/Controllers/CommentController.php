<?php

namespace App\Http\Controllers;

use App\Services\Comment\CommentService;
use App\Http\Resources\CommentResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CommentController extends Controller
{
    private CommentService $commentService;

    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
    }

    /**
     * コメント一覧取得
     */
    public function index($novelId): JsonResponse
    {
        $result = $this->commentService->getComments($novelId);

        return response()->json([
            'comments' => CommentResource::collection($result['comments']),
            'stats' => $result['stats']
        ]);
    }

    /**
     * コメント投稿
     */
    public function store(Request $request, $novelId): JsonResponse
    {
        $request->validate([
            'user_name' => 'required|string|max:50',
            'user_identifier' => 'required|string',
            'content' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:comments,id'
        ]);

        try {
            $comment = $this->commentService->createComment($novelId, $request->all());

            return (new CommentResource($comment))
                ->response()
                ->setStatusCode(201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => 'コメントの投稿に失敗しました'], 500);
        }
    }

    /**
     * コメント削除
     */
    public function destroy(Request $request, $novelId, $commentId): JsonResponse
    {
        try {
            $result = $this->commentService->deleteComment(
                $novelId,
                $commentId,
                $request->input('user_identifier')
            );

            return response()->json($result);
        } catch (\UnauthorizedHttpException $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        } catch (\Exception $e) {
            return response()->json(['error' => 'コメントの削除に失敗しました'], 500);
        }
    }

    /**
     * 人気のコメント取得
     */
    public function popular($novelId): JsonResponse
    {
        $comments = $this->commentService->getPopularComments($novelId);

        return response()->json(
            CommentResource::collection($comments)
        );
    }
}