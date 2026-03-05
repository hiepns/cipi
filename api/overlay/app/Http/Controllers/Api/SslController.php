<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CipiCliService;
use Illuminate\Http\JsonResponse;

class SslController extends Controller
{
    public function __construct(
        protected CipiCliService $cipi
    ) {}

    public function install(string $name): JsonResponse
    {
        $result = $this->cipi->sslInstall($name);
        if ($result['success']) {
            return response()->json(['data' => $result['output'], 'raw' => $result['output']], 200);
        }
        return response()->json(['error' => $result['output']], 400);
    }
}
