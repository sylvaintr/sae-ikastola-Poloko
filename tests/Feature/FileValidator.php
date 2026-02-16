<?php

namespace Tests\Feature;

class FileValidator
{
    /**
     * Validate a .docx by checking if it's a valid zip archive when needed.
     * Returns the original validity flag for non-docx files or when already valid.
     */
    public function validateDocxIfNeeded(string $filePath, string $extension, string $hexHeader, bool $isValidInitially): bool
    {
        if (strtolower($extension) !== 'docx') {
            return $isValidInitially;
        }

        if ($isValidInitially) {
            return true;
        }

        $zip = new \ZipArchive();
        $res = $zip->open($filePath);
        if ($res === true || $res === \ZipArchive::ER_OK) {
            $zip->close();
            return true;
        }

        return false;
    }
}
