<?php

use App\Mcp\Servers\ExampleServer;
use Laravel\Mcp\Server\Facades\Mcp;

// Register the example server
Mcp::web('example', ExampleServer::class);
