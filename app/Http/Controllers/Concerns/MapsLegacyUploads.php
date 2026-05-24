<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

trait MapsLegacyUploads
{
    /**
     * @return array<string, mixed>
     */
    protected function legacyFilesFromRequest(Request $request): array
    {
        $legacy = [];

        foreach ($request->allFiles() as $key => $file) {
            if ($file instanceof \Illuminate\Http\UploadedFile) {
                $legacy[$key] = [
                    'name' => $file->getClientOriginalName(),
                    'type' => $file->getClientMimeType(),
                    'tmp_name' => $file->getRealPath(),
                    'error' => $file->getError(),
                    'size' => $file->getSize(),
                ];
            }
        }

        return $legacy;
    }
}
