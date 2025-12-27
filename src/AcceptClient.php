<?php

namespace Websyspro;

use Websyspro\Logger\Enums\LogType;
use Websyspro\Logger\Log;
use Exception;
use Websyspro\Exceptions\Error;

class AcceptClient
{
  private AcceptHeader $acceptHeader;
  private Request $request;
  private Response $response;
  private DetailClient $detailClient;

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

    $this->detailClient = new DetailClient(
      $this->streamSocketAccept
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
      ]),
      $this->detailClient->getIp(),
      $this->detailClient->getPort()
    );
  }

  private function readyError(
    int $code,
    string $content,
    string|null $contentLog = null
  ): void {
    $this->response->status($code)->json(
      [ "success" => false, "content" => $content ]
    );

    Log::error(
      LogType::service,
      $contentLog ?? $content,
      $this->detailClient->getIp(),
      $this->detailClient->getPort()
    );    
  }

  private function readyRequestFail(
  ): void {
    Error::serviceUnavailableError(
      "Server is currently overloaded. Please try again later."
    );
  }

  private function readyInternalError(
    Exception $exception
  ): void {
    $this->readyError(
      $exception->getCode(), 
      "An unexpected error occurred while processing your request.",
      $exception->getMessage()
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
    try {
      if( $this->readyAccept() ){
        if( $this->readyIsMaxExceded() ){
          $this->readyRequest();
          $this->readyRequestFail();
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
    } catch( Exception $error ){
      $this->readyInternalError(
        $error
      );

      $this->readyClose();
      $this->readyDec();
    }   
  }
}