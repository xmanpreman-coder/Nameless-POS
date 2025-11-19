<?php


use App\Mcp\Servers\ExampleServer;
use Laravel\Mcp\Server\Facades\Mcp;

Mcp::web('example', ExampleServer::class); // Available at /mcp/example
