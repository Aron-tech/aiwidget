<?php

namespace App\Livewire\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

trait ImageHandlerTrait
{
    use WithFileUploads;

    /**
     * Save an image to the specified directory and update the model's attribute or JSON parameter.
     *
     * @param Model $model The Eloquent model instance.
     * @param string $attribute The attribute of the model to update.
     * @param mixed $image The image file to be saved.
     * @param string $save_dir The directory where the image will be saved.
     * @param string $disk The storage disk to use (default is 'public').
     * @param string|null $default_img The default image path to check against (optional).
     * @param string|null $json_param The JSON parameter to update if the attribute is a JSON field (optional).
     * @return bool Returns true on success, false on failure.
     */
    public function saveImage(Model $model, string $attribute, $image, string $save_dir, string $disk = 'public', ?string $default_img = null, ?string $json_param = null): bool {
        try {
            if (!$json_param) {
                if(!$image) return false;

                if ($model->{$attribute} && $model->{$attribute} !== $default_img) {
                    Storage::disk($disk)->delete($model->{$attribute});
                }

                $path = $image->store($save_dir, $disk);
                $model->{$attribute} = $path;
                $model->save();
            } else {
                $json_value = getJsonValue($model, $attribute, $json_param);

                if ($json_value && $json_value !== $default_img) {
                    Storage::disk($disk)->delete($json_value);
                }

                $path = $image->store($save_dir, $disk);
                setJsonValue($model, $attribute, $json_param, $path);
            }
            return true;
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            return false;
        }
    }
}
