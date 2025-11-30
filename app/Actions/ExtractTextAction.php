<?php

namespace App\Actions;

use App\Enums\FileTypeEnum;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser as PdfParser;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\Element\TextBreak;
use DOMDocument;
use Soundasleep\Html2Text;
use Exception;

class ExtractTextAction
{
    public function execute(string $filePath, string $disk = 'public'): ?string
    {
        try {
            $fullPath = Storage::disk($disk)->path($filePath);

            // Ellenőrizzük, hogy létezik-e a fájl
            if (!file_exists($fullPath)) {
                throw new Exception("File not found: {$fullPath}");
            }

            $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

            return match ($extension) {
                'txt', 'md' => $this->extractTextFile($fullPath),
                'docx' => $this->extractDocx($fullPath),
                'doc' => $this->extractDoc($fullPath),
                'pdf' => $this->extractPdf($fullPath),
                'html', 'htm' => $this->extractHtml($fullPath),
                'rtf' => $this->extractRtf($fullPath),
                'odt' => $this->extractOdt($fullPath),
                default => null,
            };
        } catch (Exception $e) {
            // Log the error if needed
            \Log::error("Text extraction failed for file: {$filePath}", ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Szöveges fájlok kinyerése
     * @throws Exception
     */
    private function extractTextFile(string $path): string
    {
        $content = file_get_contents($path);
        if ($content === false) {
            throw new Exception("Cannot read text file: {$path}");
        }
        return $content;
    }

    /**
     * DOCX fájlok kinyerése (ZipArchive alapú, stabil verzió)
     * @throws Exception
     */
    private function extractDocx(string $path): string
    {
        $zip = new \ZipArchive;
        if ($zip->open($path) !== true) {
            throw new \Exception("Cannot open DOCX file: {$path}");
        }

        $xmlIndex = $zip->locateName("word/document.xml");
        if ($xmlIndex === false) {
            $zip->close();
            throw new \Exception("document.xml not found in DOCX");
        }

        $xmlData = $zip->getFromIndex($xmlIndex);
        $zip->close();

        // Helyettesítések (bekezdés, táblázat, tabulátor)
        $xmlData = preg_replace('/<w:p[^>]*>/', "\n", $xmlData);
        $xmlData = preg_replace('/<w:tr>/', "\n", $xmlData);
        $xmlData = preg_replace('/<w:tab\/>/', "\t", $xmlData);
        $xmlData = preg_replace('/<\/w:p>/', "\n", $xmlData);

        $text = strip_tags($xmlData);
        $text = preg_replace('/\s+/', ' ', $text); // fölös szóközök kiszedése

        return trim($text);
    }

    /**
     * DOC fájlok kinyerése (egyszerű, bináris alapú)
     * @throws Exception
     */
    private function extractDoc(string $path): string
    {
        $fileHandle = fopen($path, "r");
        if (!$fileHandle) {
            throw new \Exception("Cannot open DOC file: {$path}");
        }

        $line = @fread($fileHandle, filesize($path));
        fclose($fileHandle);

        $lines = explode(chr(0x0D), $line);
        $outtext = "";

        foreach ($lines as $thisline) {
            $pos = strpos($thisline, chr(0x00));
            if ($pos === false && strlen($thisline) > 0) {
                $outtext .= $thisline . "\n";
            }
        }

        $text = preg_replace("/[^a-zA-Z0-9\s\,\.\-\n\r\t@\/\_\(\)]/", "", $outtext);

        return trim($text);
    }


    /**
     * RTF fájlok kinyerése
     * @throws Exception
     */
    private function extractRtf(string $path): string
    {
        try {
            $phpWord = WordIOFactory::load($path);
            $text = '';

            foreach ($phpWord->getSections() as $section) {
                $text .= $this->extractElementsText($section->getElements());
            }
            Log::info($text);
            return trim($text);
        } catch (Exception $e) {
            throw new Exception("Failed to extract RTF: " . $e->getMessage());
        }
    }

    /**
     * ODT fájlok kinyerése
     * @throws Exception
     */
    private function extractOdt(string $path): string
    {
        try {
            $phpWord = WordIOFactory::load($path);
            $text = '';

            foreach ($phpWord->getSections() as $section) {
                $text .= $this->extractElementsText($section->getElements());
            }

            return trim($text);
        } catch (Exception $e) {
            throw new Exception("Failed to extract ODT: " . $e->getMessage());
        }
    }

    /**
     * PhpWord elemekből szöveg kinyerése (rekurzív)
     */
    private function extractElementsText(array $elements): string
    {
        $text = '';

        foreach ($elements as $element) {
            if ($element instanceof Text) {
                $text .= $element->getText() . ' ';
            } elseif ($element instanceof TextRun) {
                foreach ($element->getElements() as $textElement) {
                    if ($textElement instanceof Text) {
                        $text .= $textElement->getText() . ' ';
                    }
                }
            } elseif ($element instanceof TextBreak) {
                $text .= "\n";
            } elseif (method_exists($element, 'getElements')) {
                // Rekurzív hívás beágyazott elemekhez (táblázatok, listák, stb.)
                $text .= $this->extractElementsText($element->getElements());
            } elseif (method_exists($element, 'getText')) {
                $text .= $element->getText() . ' ';
            }
        }

        return $text;
    }

    /**
     * PDF fájlok kinyerése (javított hibakezeléssel)
     */
    private function extractPdf(string $path): string
    {
        try {
            $parser = new PdfParser();
            $pdf = $parser->parseFile($path);
            $text = $pdf->getText();

            if (empty(trim($text))) {
                throw new Exception("PDF appears to be empty or contains only images");
            }

            return $text;
        } catch (Exception $e) {
            throw new Exception("Failed to extract PDF: " . $e->getMessage());
        }
    }

    /**
     * HTML fájlok kinyerése (javított hibakezeléssel)
     */
    private function extractHtml(string $path): string
    {
        try {
            $html = file_get_contents($path);
            if ($html === false) {
                throw new Exception("Cannot read HTML file: {$path}");
            }

            // Először próbáljuk a soundasleep/html2text könyvtárat
            try {
                // HTML tisztítása és hibák elnyomása
                $cleanedHtml = $this->cleanHtml($html);
                return Html2Text::convert($cleanedHtml, [
                    'ignore_errors' => true,
                    'drop_links' => false
                ]);
            } catch (Exception $e) {
                // Ha a soundasleep könyvtár nem működik, használjunk saját megoldást
                return $this->extractHtmlWithDom($html);
            }
        } catch (Exception $e) {
            throw new Exception("Failed to extract HTML: " . $e->getMessage());
        }
    }

    /**
     * HTML tisztítása a hibás címkék eltávolításával
     */
    private function cleanHtml(string $html): string
    {
        // Távolítsuk el a problémás XML deklarációt ha létezik
        $html = preg_replace('/<\?xml[^>]*>/i', '', $html);

        // Javítsuk ki a gyakori HTML hibákat
        $html = str_replace(['<header>', '</header>'], ['<div class="header">', '</div>'], $html);
        $html = str_replace(['<footer>', '</footer>'], ['<div class="footer">', '</div>'], $html);
        $html = str_replace(['<nav>', '</nav>'], ['<div class="nav">', '</div>'], $html);
        $html = str_replace(['<section>', '</section>'], ['<div class="section">', '</div>'], $html);
        $html = str_replace(['<article>', '</article>'], ['<div class="article">', '</div>'], $html);
        $html = str_replace(['<aside>', '</aside>'], ['<div class="aside">', '</div>'], $html);

        // Távolítsuk el a hibás vagy nem támogatott címkéket
        $html = preg_replace('/<\/?(?:main|figure|figcaption|time|mark|details|summary)[^>]*>/i', '', $html);

        return $html;
    }

    /**
     * HTML szöveg kinyerése DOMDocument segítségével (fallback megoldás)
     */
    private function extractHtmlWithDom(string $html): string
    {
        $dom = new DOMDocument();

        // HTML betöltése hibák elnyomásával
        libxml_use_internal_errors(true);
        $dom->loadHTML(
            '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>' . $html . '</body></html>',
            LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();

        // Script és style elemek eltávolítása
        $scripts = $dom->getElementsByTagName('script');
        while ($scripts->length > 0) {
            $scripts->item(0)->parentNode->removeChild($scripts->item(0));
        }

        $styles = $dom->getElementsByTagName('style');
        while ($styles->length > 0) {
            $styles->item(0)->parentNode->removeChild($styles->item(0));
        }

        // Szöveg kinyerése
        $text = $this->getTextFromNode($dom->documentElement);

        // Tisztítás: többszörös szóközök és sortörések eltávolítása
        $text = preg_replace('/\s+/', ' ', $text);
        $text = preg_replace('/\n\s*\n/', "\n\n", $text);

        return trim($text);
    }

    /**
     * Szöveg kinyerése DOM node-ból rekurzívan
     */
    private function getTextFromNode($node): string
    {
        $text = '';

        if ($node->nodeType === XML_TEXT_NODE) {
            return $node->nodeValue;
        }

        if ($node->nodeType === XML_ELEMENT_NODE) {
            $tagName = strtolower($node->tagName);

            // Blokk elemek után sortörés
            $blockElements = ['p', 'div', 'br', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'li', 'td', 'th'];
            $isBlock = in_array($tagName, $blockElements);

            foreach ($node->childNodes as $child) {
                $text .= $this->getTextFromNode($child);
            }

            if ($isBlock) {
                $text .= "\n";
            }
        }

        return $text;
    }

    /**
     * Ellenőrzi, hogy támogatott-e a fájlformátum
     */
    public static function isSupported(string $extension): bool
    {
        return array_key_exists(strtolower($extension), FileTypeEnum::cases());
    }
}
