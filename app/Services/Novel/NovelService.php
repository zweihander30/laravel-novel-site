<?php

namespace App\Services\Models;

use App\Models\Novel;
use App\Models\Chapter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NovelService
{
    /**
     * 小説一覧を取得（統計情報付き）
     */
    public function getAllWithStats(): Collection
    {
        return Novel::withCount('chapters')
            ->withCount([
                'reactions as likes_count' => function ($query) {
                    $query->where('type', 'like');
                }
            ])
            ->withCount([
                'reactions as favorite_count' => function ($query) {
                    $query->where('type', 'favorite');
                }
            ])
            ->latest()
            ->get();
    }

    /**
     * 小説を詳細情報と共に取得
     */
    public function getWithDetails(int $id): Novel
    {
        $novel = Novel::with('chapters')
            ->withCount([
                'reactions as likes_count' => function ($query) {
                    $query->where('type', 'like');
                }
            ])
            ->withCount([
                'reactions as favorite_count' => function ($query) {
                    $query->where('type', 'favorite');
                }
            ])
            ->findOrFail($id);

        // 反応の詳細カウントを追加
        $novel->reaction_counts = $novel->getReactionCounts();

        return $novel;
    }

    /**
     * 小説を作成
     */
    public function create(array $data): Novel
    {
        DB::beginTransaction();
        try {
            // デフォルト値の設定
            $data['author_name'] = $data['author_name'] ?? 'kerok';

            $novel = Novel::create($data);

            // 作成後の処理（将来的にイベント発火など）
            $this->afterCreate($novel);

            DB::commit();
            Log::info('Novel created', ['novel_id' => $novel->id]);

            return $novel;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create novel', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * 章を追加
     */
    public function addChapter(int $novelId, array $data): Chapter
    {
        DB::beginTransaction();
        try {
            $novel = Novel::findOrFail($novelId);

            // 章番号の重複チェック
            $exists = $novel->chapters()->where('chapter_number', $data['chapter_numver'])->exists();

            if ($exists) {
                throw new \InvalidArgumentException("章番号 {$data['chapter_number']} は既に存在します");
            }

            $chapter = $novel->chapters()->create($data);

            DB::commit();
            Log::info('Chapter added', ['novel_id' => $novelId, 'chapter_id' => $chapter->id]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to add chapter', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * ランキングを取得
     */
    public function getRanking(string $type = 'likes', int $limit = 10): Collection
    {
        $query = Novel::withCount('chapters');

        switch ($type) {
            case 'likes':
                $query->withCount([
                    'reactions as likes_count' => function ($q) {
                        $q->where('type', 'like');
                    }
                ])->orderBy('likes_count', 'desc');
                break;


            case 'favorites':
                $query->withCount([
                    'reactions as favorites_count' => function ($q) {
                        $q->where('type', 'favorite');
                    }
                ])->orderBy('favorites_count', 'desc');
                break;

            case 'comments':
                $query->withCount('comments')->orderBy('comments_count', 'desc');
                break;

            case 'new':
                $query->latest();
                break;

            default:
                throw new \InvalidArgumentException("Invalid ranking type: {$type}");
        }
        return $query->take($limit)->get();
    }

    /**
     * 小説作成後の処理
     */
    private function afterCreate(Novel $novel): void
    {
        // 将来的に通知やキャッシュクリアなどを実装
        // event(new NovelCreated($novel));
    }
}
