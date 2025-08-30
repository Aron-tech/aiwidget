<?php

namespace App\Livewire\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

trait ImageHandlerTrait
{
    use WithFileUploads;

    public function saveImage(Model $model, string $attribute, $image, string $save_dir, string $disk = 'public', ?string $default_img = null): bool
    {
        try {
            if ($model->{$attribute} && $model->{$attribute} !== $default_img) {
                Storage::disk($disk)->delete($model->{$attribute});
            }

            $path = $image->store($save_dir, $disk);
            $model->{$attribute} = $path;
            $model->save();
            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }
}
