<?php

namespace Domain\Shared\Actions;

use Illuminate\Support\Facades\Storage;
use Infra\Shared\Foundations\Action;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class UploadPhotoAction extends Action
{
    public function execute($file)
    {
        if (! $file || ! $file->isValid()) {
            throw new BadRequestException('Invalid or empty file uploaded.');
        }
        $url = Storage::disk('s3')->put('ckeditor', $file);
        $fullUrl = Storage::disk('s3')->url($url);

        return $fullUrl;

    }
}
