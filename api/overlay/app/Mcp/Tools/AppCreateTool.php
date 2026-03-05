<?php

namespace App\Mcp\Tools;

use App\Services\CipiCliService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Create a new app. Equivalent to cipi app create.')]
class AppCreateTool extends Tool
{
    public function __construct(
        protected CipiCliService $cipi
    ) {}

    public function handle(Request $request): Response
    {
        if (! $request->user()?->tokenCan('apps-create')) {
            return Response::text('Permission denied: apps-create required');
        }
        $validated = $request->validate([
            'user' => 'required|string|min:3|max:32',
            'domain' => 'required|string',
            'repository' => 'required|string',
            'branch' => 'nullable|string|max:64',
            'php' => 'nullable|string|in:7.4,8.0,8.1,8.2,8.3,8.4,8.5',
        ]);
        $result = $this->cipi->appCreate($validated);
        return Response::text($result['success'] ? $result['output'] : 'Error: ' . $result['output']);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'user' => $schema->string()->description('App username (slug)')->required(),
            'domain' => $schema->string()->description('Primary domain')->required(),
            'repository' => $schema->string()->description('Git repository URL (SSH)')->required(),
            'branch' => $schema->string()->description('Branch')->default('main'),
            'php' => $schema->string()->description('PHP version')->default('8.4'),
        ];
    }
}
