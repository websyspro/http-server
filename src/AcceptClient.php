<?php

namespace Websyspro;

use Websyspro\Logger\Enums\LogType;
use Websyspro\Logger\Log;

class AcceptClient
{
  private AcceptHeader $acceptHeader;
  private Request $request;
  private Response $response;

  public function __construct(
    private HttpServer $httpServer,
    private mixed $streamSocketAccept 
  ){
    $this->ready();
  }

  public function readyRequest(
  ): void {
    $this->response = new Response(
      $this->streamSocketAccept
    );

    $this->request = new Request(
      $this->acceptHeader = new AcceptHeader(
        $this->streamSocketAccept
      )
    );
  }  

  private function readyRequestLog(
  ): void {
    Log::message(
      LogType::service, 
      sprintf("[%s] %s - %s?%s",  ...[
        $this->acceptHeader->protocolVersion(),
        $this->acceptHeader->method(),
        $this->acceptHeader->requestUrl(),
        $this->acceptHeader->contentQuery(),
      ])
    );
  }

  private function readyErrror(
  ): void {
    $this->response
      ->status(503)
      ->json(
        [
          "success" => false,
          "content" => "Server is currently overloaded. Please try again later."
        ]
      );

    Log::error(
      LogType::service,
      "Server is currently overloaded. Please try again later."
    );
  }

  private function readyRequestSend(
  ): void {
    $this->response->json(
      [
        "success" => true,
        "content" => $this->request->query
      ]
    );    
  }

  private function readyAccept(
  ): mixed {
    return $this->streamSocketAccept;
  }

  private function readyIsMaxExceded(
  ): bool {
    return $this->httpServer->isMaxExceded();
  }

  private function readyInc(
  ): void {
    $this->httpServer->incrementConnection();
  }

  private function readyDec(
  ): void {
    $this->httpServer->decrementConnection();
  }  

  private function readyNoBlocking(
  ): void {
    @stream_set_blocking(
      $this->streamSocketAccept,
      true
    );    
  }

  public function readyClose(
  ): void {
    fflush($this->streamSocketAccept);
    fclose($this->streamSocketAccept);
  }

  public function ready(
  ): void {
    if($this->readyAccept()){
      if(!$this->readyIsMaxExceded()){
        $this->readyRequest();
        $this->readyErrror();
        $this->readyClose();
      } else {
        $this->readyInc();
        $this->readyNoBlocking();
        $this->readyRequest();
        $this->readyRequestLog();
        $this->readyRequestSend();
        $this->readyClose();
        $this->readyDec();
      }
    }
  }
}