<?php

namespace Tests\Unit\Models;

use App\Models\LessonExample;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class LessonExampleTest extends TestCase
{
    /**
     * @return array<string, array{0: string|null, 1: string|null}>
     */
    public static function youtubeUrls(): array
    {
        return [
            'watch url' => ['https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
            'watch url with extra parameters' => ['https://youtube.com/watch?feature=share&v=dQw4w9WgXcQ&t=42', 'dQw4w9WgXcQ'],
            'short link' => ['https://youtu.be/dQw4w9WgXcQ?si=abc', 'dQw4w9WgXcQ'],
            'shorts' => ['https://www.youtube.com/shorts/dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
            'embed' => ['https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
            'other site' => ['https://vimeo.com/123456789', null],
            'empty' => [null, null],
        ];
    }

    #[DataProvider('youtubeUrls')]
    public function test_extracts_the_video_id_from_youtube_urls(?string $url, ?string $expectedId): void
    {
        $this->assertSame($expectedId, LessonExample::parseYoutubeVideoId($url));
    }
}
