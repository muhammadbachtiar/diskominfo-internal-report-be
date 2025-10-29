<?php

namespace App\Exceptions;

use Defuse\Crypto\Exception\BadFormatException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $base = new BaseController;

        $this->renderable(function (ValidationException $e, $request) use ($base) {
            return $base->resolveForFailedResponseWith(
                message: 'Validation Error',
                data: $e->errors(),
                status: HttpStatus::UnprocessableEntity
            );
        });

        $this->renderable(function (NotFoundHttpException $e, $request) use ($base) {
            $message = 'Data tidak ditemukan';

            return $base->resolveForFailedResponseWith(
                message: $message,
                status: HttpStatus::NotFound
            );
        });

        $this->renderable(function (ModelNotFoundException $e, $request) use ($base) {
            return $base->resolveForFailedResponseWith(
                message: 'Data tidak ditemukan',
                status: HttpStatus::NotFound
            );
        });

        $this->renderable(function (MethodNotAllowedHttpException $e, $request) use ($base) {
            return $base->resolveForFailedResponseWith(
                message: 'Method tidak diizinkan',
                status: HttpStatus::NotAcceptable
            );
        });

        $this->renderable(function (AuthenticationException $e, $request) use ($base) {
            return $base->resolveForFailedResponseWith(
                message: 'Unauthenticated',
                status: HttpStatus::Unauthorized
            );
        });

        $this->renderable(function (AuthorizationException $e, $request) use ($base) {
            return $base->resolveForFailedResponseWith(
                message: 'Forbidden',
                status: HttpStatus::Forbidden
            );
        });

        $this->renderable(function (BadFormatException $e, $request) use ($base) {
            return $base->resolveForFailedResponseWith(
                message: $e->getMessage(),
                status: HttpStatus::Forbidden
            );
        });

        $this->renderable(function (\RuntimeException $e, $request) use ($base) {
            return $base->resolveForFailedResponseWith(
                message: $e->getMessage(),
                status: HttpStatus::BadRequest
            );
        });

        $this->renderable(function (Throwable $e, $request) use ($base) {
            $message = config('app.debug') ? $e->getMessage() : 'Internal Server Error';
            return $base->resolveForFailedResponseWith(
                message: $message,
                status: HttpStatus::InternalError
            );
        });
    }
}
