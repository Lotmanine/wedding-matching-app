<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Support\Ops\HealthSnapshotBuilder;
use Illuminate\Http\JsonResponse;

class HealthSnapshotController extends Controller
{
    public function __construct(private readonly HealthSnapshotBuilder $builder)
    {
    }

    public function __invoke(): JsonResponse
    {
        return ApiResponse::success($this->builder->build()->toArray());
    }
}
