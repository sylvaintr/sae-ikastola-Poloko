<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

trait HandlesFileValidation
{
    // Taille maximale des documents en KB (8 MB)
    // Limite sécurisée pour éviter les attaques DoS tout en permettant des documents raisonnables
    protected const MAX_DOCUMENT_SIZE_KB = 8192;

    // Magic bytes pour détecter les fichiers ZIP (utilisé pour DOCX)
    protected const ZIP_MAGIC_BYTES = '504b0304';

    /**
     * Valide l'upload d'un document.
     * La limite de taille est fixée à 8 MB (8192 KB) pour éviter les attaques DoS.
     */
    protected function validateDocumentUpload(Request $request): void
    {
        $maxSizeKB = (int) self::MAX_DOCUMENT_SIZE_KB;
        $maxSizeMB = round($maxSizeKB / 1024, 0);
        
        $request->validate([
            'document' => 'required|file|max:' . $maxSizeKB . '|mimes:pdf,doc,docx,jpg,jpeg,png',
            'idDocumentObligatoire' => 'required|integer|exists:documentObligatoire,idDocumentObligatoire',
        ], [
            'document.required' => __('auth.document_required'),
            'document.file' => __('auth.document_must_be_file'),
            'document.max' => __('auth.document_size_exceeded', ['max' => (string) $maxSizeMB]),
            'document.mimes' => __('auth.document_invalid_format'),
        ]);
    }

    /**
     * Valide l'extension du fichier.
     */
    protected function validateFileExtension(string $extension): ?\Illuminate\Http\RedirectResponse
    {
        $allowedExtensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
        if (!in_array($extension, $allowedExtensions)) {
            return Redirect::route('profile.edit')
                ->with('error', __('auth.invalid_file_format'));
        }
        return null;
    }

    /**
     * Wrapper pour la validation des magic bytes.
     */
    protected function validateFileMagicBytesWrapper($file, string $extension): ?\Illuminate\Http\RedirectResponse
    {
        $magicBytesValidation = $this->validateFileMagicBytes($file, $extension);
        if (!$magicBytesValidation['valid']) {
            return Redirect::route('profile.edit')
                ->with('error', $magicBytesValidation['message'] ?? __('auth.invalid_file_format'));
        }
        return null;
    }

    /**
     * Valide le format d'un fichier en analysant les premiers octets (magic bytes)
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $extension
     * @return array ['valid' => bool, 'message' => string|null]
     */
    protected function validateFileMagicBytes($file, string $extension): array
    {
        $result = ['valid' => false, 'message' => null];

        try {
            $hex = $this->readFileHex($file);
            if (!$hex) {
                $result['message'] = __('auth.cannot_read_file');
            } else {
                $magicBytes = $this->getMagicBytesForExtension($extension);
                if (!$magicBytes) {
                    $result['message'] = __('auth.unsupported_file_type');
                } else {
                    $valid = $this->checkMagicBytes($hex, $magicBytes);
                    $valid = $this->validateDocxIfNeeded($file, $extension, $hex, $valid);
                    $result['valid'] = $valid;
                    $result['message'] = $valid ? null : __('auth.file_type_mismatch', ['expected' => strtoupper($extension)]);
                }
            }
        } catch (\Exception $e) {
            $result['message'] = __('auth.file_validation_error');
        }

        return $result;
    }

    /**
     * Lit les premiers octets d'un fichier en hexadécimal.
     */
    protected function readFileHex($file): ?string
    {
        $handle = fopen($file->getRealPath(), 'rb');
        if (!$handle) {
            return null;
        }

        $bytes = fread($handle, 12);
        fclose($handle);

        if ($bytes === false || strlen($bytes) < 4) {
            return null;
        }

        return bin2hex($bytes);
    }

    /**
     * Retourne les magic bytes attendus pour une extension donnée.
     */
    protected function getMagicBytesForExtension(string $extension): ?array
    {
        $magicBytes = [
            'pdf' => ['25504446'], // %PDF
            'jpg' => ['ffd8ff'], // JPEG
            'jpeg' => ['ffd8ff'], // JPEG
            'png' => ['89504e47'], // PNG
            'doc' => ['d0cf11e0', '0d444f43'], // MS Office (DOC, XLS, PPT)
            'docx' => [self::ZIP_MAGIC_BYTES], // ZIP/Office Open XML (DOCX, XLSX, PPTX)
        ];

        return $magicBytes[$extension] ?? null;
    }

    /**
     * Valide un fichier DOCX si nécessaire.
     */
    protected function validateDocxIfNeeded($file, string $extension, string $hex, bool $valid): bool
    {
        if ($extension !== 'docx' || $valid || strpos($hex, self::ZIP_MAGIC_BYTES) !== 0) {
            return $valid;
        }

        if (class_exists('ZipArchive')) {
            return $this->validateDocxZip($file);
        }

        return true;
    }

    /**
     * Vérifie si les magic bytes correspondent.
     */
    protected function checkMagicBytes(string $hex, array $magicBytesList): bool
    {
        foreach ($magicBytesList as $magicHex) {
            if (strpos($hex, $magicHex) === 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Valide qu'un fichier DOCX contient bien un dossier word/
     */
    protected function validateDocxZip($file): bool
    {
        $zip = new \ZipArchive();
        if ($zip->open($file->getRealPath()) !== true) {
            return false;
        }

        $hasWord = false;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            if (strpos($zip->getNameIndex($i), 'word/') === 0) {
                $hasWord = true;
                break;
            }
        }
        $zip->close();
        return $hasWord;
    }
}
