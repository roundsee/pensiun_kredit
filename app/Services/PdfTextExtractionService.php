<?php

namespace App\Services;

use RuntimeException;

class PdfTextExtractionService
{
    /**
     * Extract normalized text lines from a text-based PDF.
     */
    public function extractLines(string $absolutePdfPath): array
    {
        if (!is_file($absolutePdfPath)) {
            throw new RuntimeException('File PDF tidak ditemukan.');
        }

        $text = $this->extractText($absolutePdfPath);
        if (trim($text) === '') {
            throw new RuntimeException('Teks PDF tidak terbaca. Pastikan PDF berbasis teks, bukan hasil scan gambar.');
        }

        return $this->normalizeLines($text);
    }

    private function extractText(string $absolutePdfPath): string
    {
        if (class_exists(\Smalot\PdfParser\Parser::class)) {
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($absolutePdfPath);
            return $pdf->getText();
        }

        return $this->extractTextFromRawPdf(file_get_contents($absolutePdfPath) ?: '');
    }

    /**
     * Fallback parser for text-based PDFs when external library is unavailable.
     */
    private function extractTextFromRawPdf(string $content): string
    {
        if ($content === '') {
            return '';
        }

        $blocks = [];
        preg_match_all('/stream\r?\n(.*?)\r?\nendstream/s', $content, $streamMatches);

        foreach (($streamMatches[1] ?? []) as $stream) {
            $decoded = $this->tryDecodeStream($stream);
            if ($decoded === '') {
                continue;
            }

            preg_match_all('/BT(.*?)ET/s', $decoded, $btBlocks);
            foreach (($btBlocks[1] ?? []) as $block) {
                $line = $this->extractTextFromTextBlock($block);
                if ($line !== '') {
                    $blocks[] = $line;
                }
            }
        }

        return implode("\n", $blocks);
    }

    private function tryDecodeStream(string $stream): string
    {
        $stream = ltrim($stream, "\r\n");

        $decoded = @gzuncompress($stream);
        if (is_string($decoded) && $decoded !== '') {
            return $decoded;
        }

        $decoded = @gzinflate($stream);
        if (is_string($decoded) && $decoded !== '') {
            return $decoded;
        }

        if (strlen($stream) > 6) {
            $decoded = @gzinflate(substr($stream, 2));
            if (is_string($decoded) && $decoded !== '') {
                return $decoded;
            }
        }

        return $stream;
    }

    private function extractTextFromTextBlock(string $block): string
    {
        $parts = [];

        preg_match_all('/\((?:\\\\.|[^\\\\)])*\)\s*Tj/s', $block, $tjMatches);
        foreach (($tjMatches[0] ?? []) as $match) {
            if (preg_match('/\((.*)\)\s*Tj/s', $match, $m)) {
                $parts[] = $this->decodePdfString($m[1]);
            }
        }

        preg_match_all('/\[(.*?)\]\s*TJ/s', $block, $tjArrayMatches);
        foreach (($tjArrayMatches[1] ?? []) as $arrayPart) {
            preg_match_all('/\((?:\\\\.|[^\\\\)])*\)/s', $arrayPart, $strMatches);
            foreach (($strMatches[0] ?? []) as $strToken) {
                $parts[] = $this->decodePdfString(substr($strToken, 1, -1));
            }
        }

        $line = trim(implode(' ', array_filter($parts, static fn ($v) => trim((string) $v) !== '')));
        return preg_replace('/\s+/', ' ', $line) ?: '';
    }

    private function decodePdfString(string $value): string
    {
        $value = str_replace(
            ['\\n', '\\r', '\\t', '\\b', '\\f', '\\\\', '\\(', '\\)'],
            ["\n", "\r", "\t", "\b", "\f", '\\', '(', ')'],
            $value
        );

        $value = preg_replace_callback('/\\\\([0-7]{1,3})/', static function ($m) {
            return chr(octdec($m[1]));
        }, $value) ?? $value;

        return $value;
    }

    private function normalizeLines(string $text): array
    {
        $rows = preg_split('/\R/', $text) ?: [];

        $lines = [];
        foreach ($rows as $line) {
            $line = trim(preg_replace('/\s+/', ' ', $line) ?? '');
            if ($line === '') {
                continue;
            }
            $lines[] = $line;
        }

        return $lines;
    }
}
