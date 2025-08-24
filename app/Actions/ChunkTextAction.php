<?php

namespace App\Actions;

class ChunkTextAction
{
    public function execute(string $text, int $chunkSize = 1000, float $overlapPercent = 0.15): array
    {
        $chunks = [];
        $overlap = (int) ($chunkSize * $overlapPercent);

        $start = 0;
        $length = mb_strlen($text);

        while ($start < $length) {
            $end = min($start + $chunkSize, $length);
            $chunk = mb_substr($text, $start, $chunkSize);

            $chunks[] = $chunk;
            $start += $chunkSize - $overlap; // 15% átfedés
        }

        return $chunks;
    }
}

