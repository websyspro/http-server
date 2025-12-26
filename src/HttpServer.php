<?php

namespace Websyspro;

use Exception;
use Websyspro\Logger\Enums\LogType;
use Websyspro\Logger\Log;

class HttpServer
{
  private int $port;
  private mixed $streamSocket;
  private string|null $errno = null;
  private string|null $error = null;
  private int $socketConnections = 0;
  private int $socketMaxConnections = 500;

  public function __construct(
  ){}

  private function streamSetBlocking(
  ): void {
    stream_set_blocking(
      $this->streamSocket,
      true
    );
  }

  private function httpClient(
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
      $this->streamSocket,
      0,
      $peername
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
  ): never {
    Log::message(
      LogType::service, 
      "Server started on port {$this->port}"
    );

    while(true){
      try {
        new ClientAccept(
          $this->httpClient(), 
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

  public function listen(
    int $port
  ): void {
    $this->port = $port;
    $this->start();
  }
}