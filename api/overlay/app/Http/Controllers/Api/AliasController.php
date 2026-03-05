<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CipiCliService;
use Illuminate\Http\JsonResponse;

class AliasController extends Controller
{
    public function __construct(
        protected CipiCliService $cipi
    ) {}

    public function list(string $name): JsonResponse
    {
        $result = $this->cipi->aliasList($name);
        if ($result['success']) {
            return response()->json(['data' => $result['output'], 'raw' => $result['output']], 200);
        }
        return response()->json(['error' => $result['output']], 400);
    }

    public function create(string $name, string $alias): JsonResponse
    {
        $result = $this->cipi->aliasAdd($name, $alias);
        if ($result['success']) {
            return response()->json(['data' => $result['output'], 'raw' => $result['output']], 200);
        }
        return response()->json(['error' => $result['output']], 400);
    }

    public function delete(string $name, string $alias): JsonResponse
    {
        $result = $this->cipi->aliasRemove($name, $alias);
        if ($result['success']) {
            return response()->json(['data' => $result['output'], 'raw' => $result['output']], 200);
        }
        return response()->json(['error' => $result['output']], 400);
    }
}
