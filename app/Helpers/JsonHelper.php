<?php


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

if (!function_exists('getJsonValue')) {
    /**
     * Visszaad egy értéket egy model JSON attribútumából.
     *
     * @param Model $model A modell
     * @param string $attribute A JSON oszlop neve a modelben
     * @param string $key A kulcs, amit ki akarunk olvasni (pontozott formátum támogatott)
     * @param mixed|null $default Alapértelmezett érték, ha nem található
     * @return mixed
     */
    function getJsonValue(Model $model, string $attribute, string $key, mixed $default = null): mixed
    {
        $jsonData = $model->{$attribute};

        if (is_string($jsonData)) {
            $jsonData = json_decode($jsonData, true);
        }

        if (!is_array($jsonData)) {
            return $default;
        }

        return Arr::get($jsonData, $key, $default);
    }
}

if (!function_exists('setJsonValue')) {
    /**
     * Beállít egy értéket egy model JSON attribútumában.
     *
     * @param Model $model A modell
     * @param string $attribute A JSON oszlop neve a modelben
     * @param string $key A kulcs, amit be akarunk állítani (pontozott formátum támogatott)
     * @param mixed $value Az új érték
     * @return void
     */
    function setJsonValue(Model $model, string $attribute, string $key, mixed $value): void
    {
        $jsonData = $model->{$attribute};

        if (is_string($jsonData)) {
            $jsonData = json_decode($jsonData, true);
        }

        if (!is_array($jsonData)) {
            $jsonData = [];
        }

        Arr::set($jsonData, $key, $value);

        $model->{$attribute} = $jsonData;
        $model->save();
    }
}
