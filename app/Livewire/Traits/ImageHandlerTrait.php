<?php

namespace App\Livewire\Traits;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

trait ImageHandlerTrait
{
    use WithFileUploads;

    public function saveImage(Model $model, string $attribute, $image, string $save_dir, string $disk = 'public', ?string $default_img = null, ?string $json_param = null, bool $use_db_transaction = false): bool
    {
        try {
            if ($use_db_transaction) {
                DB::beginTransaction();
                try {
                    if (!$json_param) {
                        if ($model->{$attribute} && $model->{$attribute} !== $default_img) {
                            Storage::disk($disk)->delete($model->{$attribute});
                        }

                        $path = $image->store($save_dir, $disk);
                        $model->{$attribute} = $path;
                        $model->save();
                    } else {
                        $json_value = getJsonValue($model, $attribute, $json_param);
                        if ($json_value && $json_value !== $default_img){
                            Storage::disk($disk)->delete($json_value);
                        }

                        $path = $image->store($save_dir, $disk);
                        setJsonValue($model, $attribute, $json_param, $path);
                    }
                    DB::commit();
                    return true;
                } catch (\Exception $e) {
                    DB::rollBack();
                    return false;
                }
            } else {
                if (!$json_param) {
                    if ($model->{$attribute} && $model->{$attribute} !== $default_img) {
                        Storage::disk($disk)->delete($model->{$attribute});
                    }

                    $path = $image->store($save_dir, $disk);
                    $model->{$attribute} = $path;
                    $model->save();
                } else {
                    $json_value = getJsonValue($model, $attribute, $json_param);
                    if ($json_value && $json_value !== $default_img){
                        Storage::disk($disk)->delete($json_value);
                    }

                    $path = $image->store($save_dir, $disk);
                    setJsonValue($model, $attribute, $json_param, $path);
                }
                return true;
            }
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            return false;
        }
    }
}
