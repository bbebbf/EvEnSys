<?php
declare(strict_types=1);

class FileTools
{
    public static function sanitizeFileName(string $fileName, string $fallback = 'attachment'): string
    {
        // Replace Windows-reserved characters: \ / : * ? " < > |
        $safe = preg_replace('/[\\\\\/:\*\?"<>\|]/', '_', $fileName);
        $safe = trim((string) $safe);

        // Rename Windows-reserved device names (with or without extension, case-insensitive)
        // e.g. CON, CON.txt, com1, LPT9.ics → _CON, _CON.txt, etc.
        if (preg_match('/^(CON|PRN|AUX|NUL|COM[1-9]|LPT[1-9])(\..+)?$/i', $safe)) {
            $safe = '_' . $safe;
        }

        // Fall back to a generic name if the result is empty
        return $safe !== '' ? $safe : $fallback;
    }
}
