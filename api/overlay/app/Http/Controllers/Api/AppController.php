<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CipiCliService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppController extends Controller
{
    public function __construct(
        protected CipiCliService $cipi
    ) {}

    public function list(): JsonResponse
    {
        $result = $this->cipi->appList();
        if ($result['success']) {
            return response()->json(['data' => $this->parseAppList($result['output']), 'raw' => $result['output']], 200);
        }
        return response()->json(['error' => $result['output']], 400);
    }

    public function show(string $name): JsonResponse
    {
        $result = $this->cipi->appShow($name);
        if ($result['success']) {
            return response()->json(['data' => $result['output'], 'raw' => $result['output']], 200);
        }
        return response()->json(['error' => $result['output']], 400);
    }

    public function create(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user' => 'required|string|min:3|max:32|regex:/^[a-z][a-z0-9]*$/',
            'domain' => 'required|string',
            'repository' => 'required|string',
            'branch' => 'nullable|string|max:64',
            'php' => 'nullable|string|in:7.4,8.0,8.1,8.2,8.3,8.4,8.5',
        ]);
        $result = $this->cipi->appCreate($validated);
        if ($result['success']) {
            return response()->json(['data' => $result['output'], 'raw' => $result['output']], 200);
        }
        return response()->json(['error' => $result['output']], 400);
    }

    public function edit(Request $request, string $name): JsonResponse
    {
        $validated = $request->validate([
            'php' => 'nullable|string|in:7.4,8.0,8.1,8.2,8.3,8.4,8.5',
            'branch' => 'nullable|string|max:64',
            'repository' => 'nullable|string',
        ]);
        $result = $this->cipi->appEdit($name, $validated);
        if ($result['success']) {
            return response()->json(['data' => $result['output'], 'raw' => $result['output']], 200);
        }
        return response()->json(['error' => $result['output']], 400);
    }

    public function delete(string $name): JsonResponse
    {
        $result = $this->cipi->appDelete($name);
        if ($result['success']) {
            return response()->json(['data' => $result['output'], 'raw' => $result['output']], 200);
        }
        return response()->json(['error' => $result['output']], 400);
    }

    private function parseAppList(string $output): array
    {
        $lines = array_filter(explode("\n", $output));
        $apps = [];
        foreach ($lines as $line) {
            if (preg_match('/^\s*[●●]\s+(\S+)\s+(\S+)\s+(\S+)\s+(.*)$/', $line, $m)) {
                $apps[] = ['app' => $m[1], 'domain' => $m[2], 'php' => $m[3], 'created' => trim($m[4])];
            }
        }
        return $apps;
    }
}
