<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller as Controller;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BaseController extends Controller
{
    /**
     * success response method.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendResponse(array|object $data, int $status = 200, $message = null): JsonResponse
    {
        $status_code = [
            200 => 'Request has been successfully',
            201 => 'Record has been saved successfully',
            204 => 'Request has been saved successfully',
            422 => 'Validations error',
            400 => 'Bad request',
            403 => 'Authorization error',
        ];

        if ($status == 204) {
            return response()->json([], $status);
        }

        $is_success = in_array($status, [200, 201]);
        $response = [
            'status'  => $status,
            'success' => $is_success,
            'message' => @$status_code[$status] ? $status_code[$status] : $message,
            'data'    => $is_success ? $data : [],
            'errors'  => !$is_success ? $data : [],
        ];


        return response()->json($response, $status);
    }
    public function paginatedResponse(LengthAwarePaginator $paginator, callable $map = null): JsonResponse
    {
        $data = $map
            ? collect($paginator->items())->map($map)->values()
            : collect($paginator->items());

        return $this->sendResponse([
            'data' => $data,
            'pagination' => [
                'totalItems' => $paginator->total(),
                'totalPages' => $paginator->lastPage(),
                'currentPage' => $paginator->currentPage(),
                'itemsPerPage' => $paginator->perPage(),
            ],
        ]);
    }
}
