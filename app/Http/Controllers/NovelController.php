<?php

namespace App\Http\Controllers;

use App\Models\Novel;
use App\Models\Chapter;
use App\Http\Requests\StoreNovelRequest;
use App\Http\Requests\StoreChapterRequest;
use App\Http\Resources\NovelResource;
use App\Http\Resources\NovelCollection;
use App\Http\Resources\ChapterResource;
use Illuminate\Http\JsonResponse;

class NovelController extends Controller
{
    // 小説一覧を取得
    public function index(): NovelCollection
    {
        $novels = Novel::withCount('chapters')
            ->withCount([
                'reactions as likes_count' => function ($query) {
                    $query->where('type', 'like');
                }
            ])
            ->withCount([
                'reactions as favorites_count' => function ($query) {
                    $query->where('type', 'favorite');
                }
            ])
            ->latest()
            ->get();

        return new NovelCollection($novels);
    }

    // 小説を作成
    public function store(StoreNovelRequest $request): JsonResponse
    {
        // バリデーション済みのデータを取得.
        $novel = Novel::create($request->validated());
        return (new NovelResource($novel))->response()->setStatusCode(201);
    }

    // 特定の小説を取得（章も含む）
    public function show($id): NovelResource
    {
        $novel = Novel::with('chapters')
            ->withCount([
                'reactions as likes_count' => function ($query) {
                    $query->where('type', 'like');
                }
            ])
            ->withCount([
                'reactions as favorites_count' => function ($query) {
                    $query->where('type', 'favorite');
                }
            ])
            ->findOrFail($id);

        // 各反応タイプの詳細な数を取得.
        $novel->reaction_counts = $novel->getReactionCounts();

        return new NovelResource($novel);
    }

    // 章を追加
    public function addChapter(StoreChapterRequest $request, $novelId): JsonResponse
    {
        $novel = Novel::findOrFail($novelId);

        $chapter = $novel->chapters()->create($request->validated());

        return (new ChapterResource($chapter))->response()->setStatusCode(201);
    }
}
