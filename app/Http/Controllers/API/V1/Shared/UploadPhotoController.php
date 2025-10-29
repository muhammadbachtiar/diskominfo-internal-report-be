<?php

namespace App\Http\Controllers\API\V1\Shared;

use Domain\Shared\Actions\UploadPhotoAction;
use Illuminate\Http\Request;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Throwable;

class UploadPhotoController extends BaseController
{
    public function __invoke(Request $req)
    {
        try {
            $data = UploadPhotoAction::resolve()->execute($req->file('upload'));

            return $this->resolveForSuccessResponseWith(
                message: 'Upload Success',
                data: ['url' => $data]
            );
        } catch (BadRequestException $th) {
            return $this->resolveForFailedResponseWith(
                message: $th->getMessage(),
                status: HttpStatus::BadRequest
            );
        } catch (Throwable $th) {
            return $this->resolveForFailedResponseWith(
                message: $th->getMessage(),
                status: HttpStatus::InternalError
            );
        }
    }
}
