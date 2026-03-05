<?php

namespace App\Mcp\Tools;

use App\Services\CipiCliService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Edit an app. Equivalent to cipi app edit <name>.')]
class AppEditTool extends Tool
{
    public function __construct(
        protected CipiCliService $cipi
    ) {}

    public function handle(Request $request): Response
    {
        if (! $request->user()?->tokenCan('apps-edit')) {
            return Response::text('Permission denied: apps-edit required');
        }
        $name = $request->get('name');
        $params = array_filter([
            'php' => $request->get('php'),
            'branch' => $request->get('branch'),
            'repository' => $request->get('repository'),
        ]);
        $result = $this->cipi->appEdit($name, $params);
        return Response::text($result['success'] ? $result['output'] : 'Error: ' . $result['output']);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('App name')->required(),
            'php' => $schema->string()->description('PHP version'),
            'branch' => $schema->string()->description('Branch'),
            'repository' => $schema->string()->description('Repository URL'),
        ];
    }
}
