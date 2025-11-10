<?php

namespace Infra\Shared\Concerns;

use Illuminate\Http\JsonResponse;
use Infra\Shared\Enums\HttpStatus;

trait InteractsWithResponse
{
    public function resolveForSuccessResponseWith(string $message, $data = null, $status = HttpStatus::Ok): JsonResponse
    {
        return response()->json(
            data: [
                'success' => true,
                'message' => $message,
                'code' => $status->value,
                'data' => $data,
            ],
            status: $status->value
        );
    }

    public function resolveForFailedResponseWith(string $message, $data = [], HttpStatus $status = HttpStatus::NotFound): JsonResponse
    {
        return response()->json(
            data: [
                'success' => false,
                'message' => $message,
                'code' => $status->value,
                'data' => (! empty($message)) ? $data : [],
            ],
            status: $status->value
        );
    }

    public function resolveForSuccessResponseWithPage(string $message, $data, $status = HttpStatus::Ok)
    {
        return response()->json(
            data: [
                'success' => true,
                'message' => $message,
                'code' => $status->value,
                'data' => $data->items(),
                'meta' => [
                    'next_page_url' => $data->nextPageUrl(),
                    'prev_page_url' => $data->previousPageUrl(),
                    'total' => $data->total(),
                    'per_page' => $data->perPage(),
                    'current_page' => $data->currentPage(),
                    'last_page' => $data->lastPage(),
                ],
            ],
            status: $status->value
        );
    }

    public function resolveForSuccessResponseWithCursorPage(string $message, $data, $status = HttpStatus::Ok)
    {
        $nextCursor = $data->nextCursor()?->encode();
        $prevCursor = $data->previousCursor()?->encode();

        $nextUrl = $nextCursor
            ? request()->fullUrlWithQuery(['cursor' => $nextCursor])
            : null;

        $prevUrl = $prevCursor
            ? request()->fullUrlWithQuery(['cursor' => $prevCursor])
        : null;
        return response()->json(
            data: [
                'success' => true,
                'message' => $message,
                'code' => $status->value,
                'data' => $data->items(),
                'meta' => [
                    'next_page_url' => $nextUrl,
                    'prev_page_url' => $prevUrl,
                    'per_page' => $data->perPage(),
                ],
            ],
            status: $status->value
        );
    }
}
