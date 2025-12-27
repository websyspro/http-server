<?php

namespace Websyspro;

use Exception;
use Websyspro\Logger\Enums\LogType;
use Websyspro\Logger\Log;

class HttpServer
{
  private bool $running = true;
  private int $port;
  private mixed $streamSocket;
  private string|null $errno = null;
  private string|null $error = null;
  private int $socketConnections = 0;
  private int $socketMaxConnections = 500;


  private function startShutdown(
  ): void {
    if( function_exists( "pcntl_async_signals" )){
      if( function_exists( "pcntl_signal" )){
        if( defined( "SIGTERM" ) && defined( "SIGINT" )){
          pcntl_async_signals(true);

          pcntl_signal(SIGTERM, fn() => $this->shutdown());
          pcntl_signal(SIGINT, fn() => $this->shutdown());
        }
      }
    }
  }

  private function streamSetBlocking(
  ): void {
    @stream_set_blocking(
      $this->streamSocket,
      true
    );
  }

  private function httpServer(
  ): HttpServer {
    return $this;
  }
  
  private function streamSocketServer(
  ): mixed {
    return stream_socket_server(
      "tcp://0.0.0.0:{$this->port}", 
      $this->errno,
      $this->error
    );    
  }

  private function streamSocketAccept(
  ): mixed {
    return @stream_socket_accept(
      $this->streamSocket, 1
    );
  }

  public function isConnectionExceded(
  ): bool {
    return $this->socketConnections >= $this->socketMaxConnections;
  }

  public function incrementConnection(
  ): void {
    $this->socketConnections++;
  }

  public function decrementConnection(
  ): void {
    $this->socketConnections--;
  } 
  
  private function startLoop(
  ) {
    Log::message(
      LogType::service, 
      "Server started on port {$this->port}"
    );

    while($this->running){
      try {
        new AcceptClient(
          $this->httpServer(), 
          $this->streamSocketAccept()
        );
      } catch (Exception $error) {
        throw new Exception(
          $error
        );
      }
    }
  }


  private function start(
  ): void {
    $this->streamSocket = $this->streamSocketServer();
    if ($this->streamSocket === false) {
      throw new Exception(
        "Error: {$this->errno} - {$this->error}"
      );
    }

    $this->streamSetBlocking();
    $this->startLoop();
  }

  public function shutdown(
  ): void {
    Log::message(
      LogType::service,
      "Shutdown iniciado"
    );

    $this->running = false;
    if(is_resource($this->streamSocket)){
      fclose($this->streamSocket);
    }
  } 

  public function listen(
    int $port
  ): void {
    $this->port = $port;
    $this->startShutdown();
    $this->start();
  }
}