<?php
// tests/Unit/WikitextLiteIntroServiceTest.php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\WikiIntro\WikitextLiteIntroService;
use PHPUnit\Framework\Attributes\Test;

class WikitextLiteIntroServiceTest extends TestCase
{
    private WikitextLiteIntroService $svc;

    protected function setUp(): void
    {
        parent::setUp();
        $this->svc = app(WikitextLiteIntroService::class);
    }

    /** @test */
    public function it_converts_basic_wikitext_to_plain_intro()
    {
        $wikitext = "'''ルー・テーズ杯争奪リーグ戦'''（ルー・テーズはいそうだつリーグせん）は、[[1983年]]に若手選手によるリーグ戦が開催された。";
        $intro = $this->svc->intro($wikitext, 50);

        $this->assertStringStartsWith("ルー・テーズ杯争奪リーグ戦（ルー・テーズはいそうだつリーグせん）は、1983年", $intro);
        $this->assertStringContainsString("開催された", $intro);
    }

    #[Test]
    public function it_trims_to_n_characters()
    {
        $wikitext = "'''テスト大会'''は、とてもとても長い説明文がここに続いています。さらに文章が続いていきます。";
        $intro = $this->svc->intro($wikitext, 20);

        // 20文字＋「…」になっているはず
        $this->assertLessThanOrEqual(21, mb_strlen($intro));
        $this->assertStringEndsWith("…", $intro);
    }

    #[Test]
    public function it_handles_links_and_formatting()
    {
        $wikitext = "これは[[リンク先|リンクテキスト]]を含み、''斜体''や'''太字'''も含みます。";
        $intro = $this->svc->intro($wikitext, 100);

        $this->assertStringContainsString("リンクテキスト", $intro);
        $this->assertStringNotContainsString("[[", $intro); // wiki記法が残っていないこと
    }

    #[Test]
    public function it_extracts_only_first_sentence()
    {
        $wikitext = "最初の文です。次の文です。さらに文が続きます。";
        $intro = $this->svc->intro($wikitext, 100);

        $this->assertEquals("最初の文です。", $intro);
    }
}

