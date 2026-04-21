<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\WedRequestResource;
use App\Http\Responses\ApiResponse;
use App\Models\WedRequest;
use Illuminate\Http\JsonResponse;

class WedRequestController extends Controller
{
    public function show(string $wedUid): JsonResponse
    {
        $wr = WedRequest::where('wed_uid', $wedUid)->with('serviceRequests')->first();
        if (! $wr) {
            return ApiResponse::error('not_found', 'Wed request not found', [], 404);
        }

        return ApiResponse::success((new WedRequestResource($wr))->resolve(request()));
    }
}
