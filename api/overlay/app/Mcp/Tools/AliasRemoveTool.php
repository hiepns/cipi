<?php

namespace App\Mcp\Tools;

use App\Services\CipiCliService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;
use Laravel\Mcp\Server\Tool;

#[Description('Remove alias from app. Equivalent to cipi alias remove <app> <alias>.')]
#[IsDestructive]
class AliasRemoveTool extends Tool
{
    public function __construct(
        protected CipiCliService $cipi
    ) {}

    public function handle(Request $request): Response
    {
        if (! $request->user()?->tokenCan('aliases-delete')) {
            return Response::text('Permission denied: aliases-delete required');
        }
        $name = $request->get('name');
        $alias = $request->get('alias');
        $result = $this->cipi->aliasRemove($name, $alias);
        return Response::text($result['success'] ? $result['output'] : 'Error: ' . $result['output']);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('App name')->required(),
            'alias' => $schema->string()->description('Domain alias to remove')->required(),
        ];
    }
}
