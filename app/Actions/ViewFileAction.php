<?php

namespace App\Actions;

use Illuminate\Http\Request as HttpRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ViewFileAction
{
    use AsAction;

    public function handle(HttpRequest $request): BinaryFileResponse
    {
        $path = $request->query('path');

        $fullPath = Storage::disk('public')->path($path);

        if (!file_exists($fullPath)) {
            abort(404);
        }

        return response()->file($fullPath);
    }

    public function htmlResponse($result)
    {
        return $result;
    }
}
