<?php

namespace App\Actions;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class UploadFileAction
{
    public function execute(int $site_id, UploadedFile $file, string $directory = 'documents', string $disk = 'public', bool $is_filename = false): ?string {
        try {
            if ($is_filename) {
                $file_name = time() . '_' . $file->getClientOriginalName();
                return $file->storeAs("uploads/{$site_id}/{$directory}", $file_name, $disk);
            } else {
                return $file->store("uploads/{$site_id}/{$directory}", $disk);
            }
        } catch (\Exception $e) {
            Log::error("File upload failed: " . $e->getMessage());
            return null;
        }
    }
}

