<?php

namespace App\Livewire\Traits;

use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\WithFileUploads;

trait FileHandlerTrait
{
    use WithFileUploads;
    public function downloadFile(string $file_path, string $disk = 'public') : ?string
    {
        try {
            if (Storage::disk($disk)->exists($file_path)) {
                return Storage::disk($disk)->download($file_path);
            }
            return null;
        } catch (\Exception $e) {
            \Log::error("File download failed: " . $e->getMessage());
            return null;
        }
    }

    public function deleteFile(string $file_path, string $disk = 'public') : bool
    {
        try {
            if (Storage::disk($disk)->exists($file_path)) {
                return Storage::disk($disk)->delete($file_path);
            }
            return false;
        } catch (\Exception $e) {
            \Log::error("File deletion failed: " . $e->getMessage());
            return false;
        }
    }
}
