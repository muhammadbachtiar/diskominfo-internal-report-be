<?php

namespace Infra\Shared\Concerns;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Infra\Shared\Enums\HttpStatus;

trait InteractWithFailedValidation
{
    use InteractsWithResponse;

    /**
     * Handle a failed validation attempt.
     *
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response: $this->resolveForFailedResponseWith(
                message: 'Given request are invalid',
                data: $validator->errors()->toArray(),
                status: HttpStatus::UnprocessableEntity
            )
        );
    }
}
