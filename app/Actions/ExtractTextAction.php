<?php

namespace App\Actions;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use Smalot\PdfParser\Parser as PdfParser;
use DOMDocument;

class ExtractTextAction
{
    public function execute(string $filePath, string $disk = 'public'): ?string
    {
        $fullPath = Storage::disk($disk)->path($filePath);
        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

        return match ($extension) {
            'txt', 'md' => file_get_contents($fullPath),
            'docx', 'doc' => $this->extractDocx($fullPath),
            'pdf' => $this->extractPdf($fullPath),
            'html', 'htm' => $this->extractHtml($fullPath),
            default => null,
        };
    }

    private function extractDocx(string $path): string
    {
        $phpWord = \PhpOffice\PhpWord\IOFactory::load($path);
        $text = '';
        foreach ($phpWord->getSections() as $section) {
            $elements = $section->getElements();
            foreach ($elements as $element) {
                if (method_exists($element, 'getText')) {
                    $text .= $element->getText() . "\n";
                }
            }
        }
        return $text;
    }

    /**
     * @throws \Exception
     */
    private function extractPdf(string $path): string
    {
        $parser = new PdfParser();

        if (!file_exists($path)) {
            throw new \Exception("PDF file not found: {$path}");
        }

        $pdf = $parser->parseFile($path);
        return $pdf->getText();
    }

    private function extractHtml(string $path): string
    {
        $html = file_get_contents($path);
        $dom = new DOMDocument();
        $dom->loadHTML($html);

        return $dom->textContent;
    }
}
