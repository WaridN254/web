<?php

namespace App\Console\Commands;

use App\Models\ScanQueue;
use Illuminate\Console\Command;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use React\EventLoop\Loop;

class ScannerWebSocketServer extends Command
{
    protected $signature = 'scanner:websocket {--port=8432}';
    protected $description = 'Start WebSocket server for phone barcode scanner on port 8432';

    public function handle(): int
    {
        $port = (int) $this->option('port');

        $this->info("Starting scanner WebSocket server on port {$port}...");
        $this->info("Phone URL: http://<your-ip>:{$port}");
        $this->info("WebSocket: ws://<your-ip>:{$port}/ws");

        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    new ScannerWebSocketHandler()
                )
            ),
            $port
        );

        $server->run();

        return Command::SUCCESS;
    }
}
