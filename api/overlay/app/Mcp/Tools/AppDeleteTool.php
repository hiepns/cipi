<?php

namespace App\Mcp\Tools;

use App\Services\CipiCliService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;
use Laravel\Mcp\Server\Tool;

#[Description('Delete an app. Equivalent to cipi app delete <name>.')]
#[IsDestructive]
class AppDeleteTool extends Tool
{
    public function __construct(
        protected CipiCliService $cipi
    ) {}

    public function handle(Request $request): Response
    {
        if (! $request->user()?->tokenCan('apps-delete')) {
            return Response::text('Permission denied: apps-delete required');
        }
        $name = $request->get('name');
        $result = $this->cipi->appDelete($name);
        return Response::text($result['success'] ? $result['output'] : 'Error: ' . $result['output']);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('App name')->required(),
        ];
    }
}
