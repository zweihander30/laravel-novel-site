<?php

namespace App\Http\Controllers;

use App\Models\Novel;
use App\Models\Chapter;
use Illuminate\Http\Request;

class NovelController extends Controller
{
    // 小説一覧を取得
    public function index()
    {
        $novels = Novel::withCount('chapters')->get();
        return response()->json($novels);
    }

    // 小説を作成
    public function store(Request $request)
    {
        $novel = Novel::create([
            'title' => $request->title,
            'description' => $request->description,
            'author_name' => $request->author_name ?? 'kerok'
        ]);
        
        return response()->json($novel, 201);
    }

    // 特定の小説を取得（章も含む）
    public function show($id)
    {
        $novel = Novel::with('chapters')->findOrFail($id);
        return response()->json($novel);
    }

    // 章を追加
    public function addChapter(Request $request, $novelId)
    {
        $novel = Novel::findOrFail($novelId);
        
        $chapter = Chapter::create([
            'novel_id' => $novelId,
            'chapter_number' => $request->chapter_number,
            'title' => $request->title,
            'content' => $request->content
        ]);
        
        return response()->json($chapter, 201);
    }
}
