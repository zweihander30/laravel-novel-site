<?php
// app/Http/Controllers/ArticleController.php
namespace App\Http\Controllers;

use App\Models\Article;
use App\Services\WikiIntro\WikitextLiteIntroService;

class ArticleController extends Controller
{
    public function index(WikitextLiteIntroService $svc)
    {
        $articles = Article::latest()->take(10)->get(); // 10件
        $N = 50;

        // 各記事の lead を生成（キャッシュが効くので速い）
        $leads = [];
        foreach ($articles as $a) {
            $leads[$a->id] = $svc->intro($a->body, $N);
        }

        return view('articles.index', compact('articles', 'leads'));
    }
}

