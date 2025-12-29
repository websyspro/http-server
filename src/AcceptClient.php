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
      sprintf(
        "[%s] %s %s%s%s",  ...[
          $this->acceptHeader->protocolVersion(),
          $this->acceptHeader->requestMethod(),
          $this->acceptHeader->requestUrl(),
          $this->acceptHeader->requestUrlSeparator(),
          $this->acceptHeader->requestUrlQuery(),
        ]
      )
    );
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

  private function readyRequestSend(
  ): void {
    $routers = $this->httpServer->getRouters()
      ->where(fn(Router $router) => (
        $router->isValid(
          $this->acceptHeader->requestMethod(), 
          $this->acceptHeader->requestUrl(),
        )
      ));

    if($routers->exist() === false ){
      Error::notFound( "Controller not found" );
    }

    if($routers->first() instanceof Router) {
      $routers->first()->execute(
        $this->response,
        $this->request
      );
    } else {
      $this->response->json(
        $this->request->query
      );
    }
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
        if( $this->readyIsMaxExceded()){
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