<?php

namespace App\Services\Reaction;

use App\Models\Novel;
use App\Models\Reaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReactionService
{
    const ALLOWED_TYPES = ['like', 'favorite', 'amazing', 'want_to_read'];

    /**
     * 反応をトグル（追加/削除）
     */
    public function toggleReaction(int $novelId, string $type, string $userIdentifier): array
    {
        // 反応タイプの検証
        if (!in_array($type, self::ALLOWED_TYPES)) {
            throw new \InvalidArgumentException("無効な反応タイプです: {$type}");
        }

        DB::beginTransaction();
        try {
            $novel = Novel::findOrFail($novelId);
            
            $existing = Reaction::where('novel_id', $novelId)
                ->where('user_identifier', $userIdentifier)
                ->where('type', $type)
                ->first();
            
            if ($existing) {
                $existing->delete();
                $action = 'removed';
                Log::info('Reaction removed', [
                    'novel_id' => $novelId,
                    'type' => $type,
                    'user' => $userIdentifier
                ]);
            } else {
                Reaction::create([
                    'novel_id' => $novelId,
                    'user_identifier' => $userIdentifier,
                    'type' => $type
                ]);
                $action = 'added';
                Log::info('Reaction added', [
                    'novel_id' => $novelId,
                    'type' => $type,
                    'user' => $userIdentifier
                ]);
            }
            
            // 更新後の反応数を取得
            $counts = $this->getReactionCounts($novelId);
            
            DB::commit();
            
            return [
                'action' => $action,
                'counts' => $counts
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to toggle reaction', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * 小説の反応統計を取得
     */
    public function getStats(int $novelId): array
    {
        $novel = Novel::findOrFail($novelId);
        
        return [
            'novel_id' => $novelId,
            'counts' => $this->getReactionCounts($novelId),
            'total_reactions' => $novel->reactions()->count(),
            'unique_users' => $novel->reactions()
                ->select('user_identifier')
                ->distinct()
                ->count()
        ];
    }

    /**
     * 反応のカウントを取得
     */
    private function getReactionCounts(int $novelId): array
    {
        $counts = Reaction::where('novel_id', $novelId)
            ->select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();
        
        // すべてのタイプに対して0を設定
        $result = [];
        foreach (self::ALLOWED_TYPES as $type) {
            $result[$type] = $counts[$type] ?? 0;
        }
        
        return $result;
    }
}