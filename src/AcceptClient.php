<?php

namespace Websyspro;

use Websyspro\Logger\Enums\LogType;
use Websyspro\Exceptions\Error;
use Websyspro\Logger\Log;
use Exception;

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
    if($this->acceptHeader->isContent()) {
      Log::message(
        LogType::service, 
        sprintf(
          "[%s] %s %s",  ...[
            $this->acceptHeader->getProtocolVersion(),
            $this->acceptHeader->getRequestMethod(),
            $this->acceptHeader->getRequestUrlFull()
          ]
        )
      );
    }
  }

  private function readyError(
    int $code,
    string $message,
    string|null $messageLog = null
  ): void {
    $this->response
      ->status($code)
      ->json(
        $message
      );

    Log::error(
      LogType::service,
      $messageLog ?? $message
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
    [ $code, $message ] = [ 
      $exception->getCode(),
      $exception->getMessage()
    ];

    if(in_array($exception->getCode(), [
      Response::HTTP_INTERNAL_SERVER_ERROR
    ])){
      $this->readyError(
        $code, 
        "An unexpected error occurred while processing your request.",
        $message
      );
    } else {
      $this->readyError(
        $code, 
        $message
      );
    }
  }

  private function getAcceptClient(
  ): AcceptClient {
    return $this;
  }  

  public function getResponse(
  ): Response {
    return $this->getAcceptClient()->response;
  }

  public function getRequest(
  ): Request {
    return $this->getAcceptClient()->request;
  }  
  
  private function readyRequestSend(
  ): void {
    if($this->acceptHeader->isContent()){
      $routers = $this->httpServer->getRouters()
        ->where(fn(Router $router) => (
          $router->isValid(
            $this->acceptHeader->getRequestMethod(), 
            $this->acceptHeader->getRequestUrl(),
          )
        ));

      if( $routers->exist() === false ){
        Error::notFound( "Controller not found" );
      }

      [ $router ] = $routers->all();
      if($router instanceof Router) {
        $router->execute(
          $this,
          $this->acceptHeader
        );
      } else {
        $this->getResponse()->json(
          $this->request->query
        );
      }
    }
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
      false
    );    
  }

  public function readyClose(
  ): void {
    fflush($this->streamSocketAccept);
    fclose($this->streamSocketAccept);
  }

  private function setReadyExceded(
  ): void {
    $this->readyRequest();
    $this->readyRequestFail();
    $this->readyClose();
  }

  private function setReadyToClient(
  ): void {
    $this->readyInc();
    $this->readyNoBlocking();
    $this->readyRequest();
    $this->readyRequestLog();
    $this->readyRequestSend();
    $this->readyClose();
    $this->readyDec();
  }

  public function ready(
  ): void {
    try {
      $this->readyIsMaxExceded()
        ? $this->setReadyExceded()
        : $this->setReadyToClient();
    } catch( Exception $error ){
      $this->readyInternalError(
        $error
      );

      $this->readyClose();
      $this->readyDec();
    }   
  }
}