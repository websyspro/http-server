<?php

use Websyspro\HttpServer;

$httpServer = new HttpServer();
$httpServer->listen( 3002 );

/*
/* Reutilizar conexões existentes ao invés de criar novas
/* POSSIVEIS MELHORIAS - 1
class ConnectionPool {
    private $pool = [];
    private $maxConnections = 100;
    
    public function getConnection() {
        if (!empty($this->pool)) {
            return array_pop($this->pool);
        }
        return $this->createNewConnection();
    }
    
    public function releaseConnection($connection) {
        if (count($this->pool) < $this->maxConnections) {
            $this->pool[] = $connection;
        }
    }
}

/* POSSIVEIS MELHORIAS - 2
/* Usar ReactPHP ou Swoole para I/O não-bloqueante
use React\Socket\Server;
use React\Http\Server as HttpServer;

$server = new HttpServer(function ($request) {
    // Processa requisições sem bloquear outras
    return new Promise(function ($resolve) use ($request) {
        // Processamento assíncrono aqui
        $resolve(new Response(200, [], 'OK'));
    });
});

/* POSSIVEIS MELHORIAS - 3
/* Múltiplos processos worker para distribuir carga
for ($i = 0; $i < 4; $i++) {
    $pid = pcntl_fork();
    if ($pid == 0) {
        // Processo filho - worker
        $this->startWorker();
        exit;
    }
}


*/
