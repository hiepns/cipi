<?php

namespace App\Mcp\Tools;

use App\Services\CipiCliService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Install SSL certificate for app. Equivalent to cipi ssl install <name>.')]
class SslInstallTool extends Tool
{
    public function __construct(
        protected CipiCliService $cipi
    ) {}

    public function handle(Request $request): Response
    {
        if (! $request->user()?->tokenCan('ssl-manage')) {
            return Response::text('Permission denied: ssl-manage required');
        }
        $name = $request->get('name');
        $result = $this->cipi->sslInstall($name);
        return Response::text($result['success'] ? $result['output'] : 'Error: ' . $result['output']);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('App name')->required(),
        ];
    }
}
