<?php

namespace App\Http\Controllers;

use App\Models\Novel;
use App\Models\Reaction;
use Illuminate\Http\Request;

class ReactionController extends Controller
{
    // 反応を追加/削除（トグル）
    public function toggle(Request $request, $novelId)
    {
        $request->validate([
            'type' => 'required|in:like,favorite,amazing,want_to_read',
            'user_identifier' => 'required|string'
        ]);

        $novel = Novel::findOrFail($novelId);

        $existing = Reaction::where('novel_id', $novelId)
            ->where('user_identifier', $request->user_identifier)
            ->where('type', $request->type)
            ->first();

        if($existing){
            $existing->delete();
            $action = 'removed';
        }else{
            Reaction::create([
                'novel_id' => $novelId,
                'user_identifier' => $request->user_identifier,
                'type' => $request->type
            ]);
            $action = 'added';
        }

        // 更新後の反応数を取得.
        $counts = $novel->getReactionCounts();

        return response()->json([
            'action' => $action,
            'counts' => $counts
        ]);
    }

    // 小説の反応統計を取得.
    public function stats($novelId)
    {
        $novel = Novel::findOrFail($novelId);
        $counts = $novel->getReactionCounts();

        return response()->json([
            'novel_id' => $novelId,
            'counts' => $counts
        ]);
    }
}
