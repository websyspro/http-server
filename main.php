<?php

use Websyspro\HttpServer;

$httpServer = new HttpServer();
$httpServer->listen( 3002 );

/*
  if (PHP_OS_FAMILY !== 'Windows' && function_exists('pcntl_async_signals')) {
      pcntl_async_signals(true);
      pcntl_signal(SIGTERM, fn() => $server->shutdown());
      pcntl_signal(SIGINT, fn() => $server->shutdown());
  }
*/