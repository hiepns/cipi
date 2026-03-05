<?php

use App\Mcp\Servers\CipiServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::web('/mcp', CipiServer::class)
    ->middleware(['auth:sanctum', 'ability:mcp-access']);
