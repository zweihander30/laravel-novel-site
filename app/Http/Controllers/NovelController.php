<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNovelRequest;
use App\Http\Requests\StoreChapterRequest;
use App\Http\Resources\NovelResource;
use App\Http\Resources\NovelCollection;
use App\Http\Resources\ChapterResource;
use App\Services\Novel\NovelService;
use Illuminate\Http\JsonResponse;

use Illuminate\Support\Facades\Log;


class NovelController extends Controller
{
    private NovelService $novelService;

    public function __construct(NovelService $novelService)
    {
        $this->novelService = $novelService;
    }

    /**
     * 小説一覧を取得
     */
    public function index(): NovelCollection
    {
        $novels = $this->novelService->getAllWithStats();
        return new NovelCollection($novels);
    }

    /**
     * 小説を作成
     */
    public function store(StoreNovelRequest $request): JsonResponse
    {
        try {
            Log::debug('test1', [$request]);
            $novel = $this->novelService->create($request->validated());
            
            return (new NovelResource($novel))
                ->response()
                ->setStatusCode(201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * 特定の小説を取得
     */
    public function show($id): NovelResource
    {
        $novel = $this->novelService->getWithDetails($id);
        return new NovelResource($novel);
    }

    /**
     * 章を追加
     */
    public function addChapter(StoreChapterRequest $request, $novelId): JsonResponse
    {
        try {
            $chapter = $this->novelService->addChapter($novelId, $request->validated());
            
            return (new ChapterResource($chapter))
                ->response()
                ->setStatusCode(201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'error' => '章の追加に失敗しました'
            ], 500);
        }
    }
}