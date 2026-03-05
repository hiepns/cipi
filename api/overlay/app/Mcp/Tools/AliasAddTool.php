<?php

namespace App\Mcp\Tools;

use App\Services\CipiCliService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Add alias to app. Equivalent to cipi alias add <app> <alias>.')]
class AliasAddTool extends Tool
{
    public function __construct(
        protected CipiCliService $cipi
    ) {}

    public function handle(Request $request): Response
    {
        if (! $request->user()?->tokenCan('aliases-create')) {
            return Response::text('Permission denied: aliases-create required');
        }
        $name = $request->get('name');
        $alias = $request->get('alias');
        $result = $this->cipi->aliasAdd($name, $alias);
        return Response::text($result['success'] ? $result['output'] : 'Error: ' . $result['output']);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('App name')->required(),
            'alias' => $schema->string()->description('Domain alias')->required(),
        ];
    }
}
