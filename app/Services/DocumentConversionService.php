<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Chapter;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;

class DocumentConversionService
{
    public function convert(Book $book, UploadedFile $file): Chapter
    {
        Settings::setOutputEscapingEnabled(true);

        $reader = strtolower($file->getClientOriginalExtension()) === 'doc' ? 'MsDoc' : 'Word2007';
        $phpWord = IOFactory::load($file->getRealPath(), $reader);

        $html = $this->toHtml($phpWord);
        $pages = $this->splitIntoPages($html);

        $position = ((int) $book->chapters()->max('position')) + 1;

        $chapter = $book->chapters()->create([
            'title' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'position' => $position,
        ]);

        foreach ($pages as $index => $content) {
            $chapter->pages()->create([
                'position' => $index + 1,
                'content' => $content,
            ]);
        }

        return $chapter->load('pages');
    }

    private function toHtml($phpWord): string
    {
        $writer = IOFactory::createWriter($phpWord, 'HTML');

        ob_start();
        $writer->save('php://output');
        $full = (string) ob_get_clean();

        if (preg_match('/<body[^>]*>(.*)<\/body>/is', $full, $matches)) {
            return trim($matches[1]);
        }

        return trim($full);
    }

    private function splitIntoPages(string $html): array
    {
        $segments = preg_split('/<hr[^>]*>|<!--\s*pagebreak\s*-->/i', $html) ?: [$html];

        $pages = [];

        foreach ($segments as $segment) {
            $segment = trim($segment);

            if ($segment !== '') {
                $pages[] = $segment;
            }
        }

        return $pages === [] ? [$html] : $pages;
    }
}
