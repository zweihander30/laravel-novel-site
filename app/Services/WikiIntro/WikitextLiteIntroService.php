<?php
// app/Services/WikiIntro/WikitextLiteIntroService.php
namespace App\Services\WikiIntro;

use Trismegiste\Wikitext\Wikitext;
use Illuminate\Support\Facades\Cache;

class WikitextLiteIntroService
{
    public function __construct(private Wikitext $parser = new Wikitext()) {}

    public function intro(string $wikitext, int $limit = 50): string
    {
        // 1) まずはキャッシュ（高速化 & 安定）
        $key = 'wikitext:intro:' . md5($wikitext) . ":$limit";
        return Cache::remember($key, now()->addMinutes(30), function () use ($wikitext, $limit) {
            // 2) Wikitext → HTML（ライブラリで変換）
            $html = $this->parser->render($wikitext) ?? '';

            // 3) HTML → プレーン
            $plain = trim(preg_replace('/\s+/u', ' ', strip_tags($html)));

            // 4) 先頭1文だけ取り出す（記号で区切り）
            $sentence = $this->firstSentence($plain);

            // 5) N文字で省略（絵文字等にもなるべく優しい）
            if (function_exists('grapheme_strlen')) {
                if (grapheme_strlen($sentence) > $limit) {
                    return grapheme_substr($sentence, 0, $limit) . '…';
                }
                return $sentence;
            }
            return mb_strimwidth($sentence, 0, $limit, '…', 'UTF-8');
        });
    }

    private function firstSentence(string $t): string
    {
        foreach (['。','．'] as $m) { // 日本語句点優先
            $p = mb_strpos($t, $m);
            if ($p !== false) return mb_substr($t, 0, $p + 1, 'UTF-8');
        }
        foreach (['.','！','!','？','?'] as $m) {
            $p = mb_strpos($t, $m);
            if ($p !== false) return mb_substr($t, 0, $p + 1, 'UTF-8');
        }
        return $t;
    }
}

