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

  public function readyNoBlocking(
  ): void {
    @stream_set_blocking(
      $this->streamSocketAccept,
      false
    );    
  }

  public function closeRequest(
  ): void {
    fflush($this->streamSocketAccept);
    fclose($this->streamSocketAccept);
  }

  public function ready(
  ): void {
    if ($this->streamSocketAccept) {
      if ($this->httpServer->isConnectionExceded()) {
        // TD para retorno de error Service Unavailable
        echo "Connection exceded\n";
      } else {
        $this->httpServer->incrementConnection();
        $this->readyNoBlocking();
        $this->readyRequest();
        $this->readyRequestLog();

        // $this->response->json(
        //   [
        //     "query" => $this->request->query,
        //     "files" => $this->request->files,
        //     "body"  => $this->request->body,
        //   ]
        // );

        $this->response->json(
          value: [
            "success" => true,
            "content" => $this->request->query
          ]
        );        

        $this->closeRequest();
        $this->httpServer->decrementConnection();
      }
    }
  }
}