<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// テスト用のユーザーデータAPI
Route::get('/users', function () {
    return response()->json([
        ['id' => 1, 'name' => '田中太郎', 'email' => 'tanaka@example.com'],
        ['id' => 2, 'name' => '鈴木花子', 'email' => 'suzuki@example.com'],
        ['id' => 3, 'name' => '佐藤次郎', 'email' => 'sato@example.com'],
    ]);
});

// 特定のユーザーを取得
Route::get('/users/{id}', function ($id) {
    $users = [
        1 => ['id' => 1, 'name' => '田中太郎', 'email' => 'tanaka@example.com'],
        2 => ['id' => 2, 'name' => '鈴木花子', 'email' => 'suzuki@example.com'],
        3 => ['id' => 3, 'name' => '佐藤次郎', 'email' => 'sato@example.com'],
    ];
    
    if (isset($users[$id])) {
        return response()->json($users[$id]);
    }
    
    return response()->json(['error' => 'User not found'], 404);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// 小説関連のAPIルート
Route::get('/novels', [App\Http\Controllers\NovelController::class, 'index']);
Route::post('/novels', [App\Http\Controllers\NovelController::class, 'store']);
Route::get('/novels/{id}', [App\Http\Controllers\NovelController::class, 'show']);
Route::post('/novels/{id}/chapters', [App\Http\Controllers\NovelController::class, 'addChapter']);
