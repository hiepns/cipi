<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\AliasAddTool;
use App\Mcp\Tools\AliasListTool;
use App\Mcp\Tools\AliasRemoveTool;
use App\Mcp\Tools\AppCreateTool;
use App\Mcp\Tools\AppDeleteTool;
use App\Mcp\Tools\AppEditTool;
use App\Mcp\Tools\AppListTool;
use App\Mcp\Tools\AppShowTool;
use App\Mcp\Tools\SslInstallTool;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;
use Laravel\Mcp\Server;

#[Name('Cipi Server')]
#[Version('1.0.0')]
#[Instructions('Cipi server management: apps, aliases, SSL. Uses same token as REST API.')]
class CipiServer extends Server
{
    protected array $tools = [
        AppListTool::class,
        AppShowTool::class,
        AppCreateTool::class,
        AppEditTool::class,
        AppDeleteTool::class,
        AliasListTool::class,
        AliasAddTool::class,
        AliasRemoveTool::class,
        SslInstallTool::class,
    ];
}
