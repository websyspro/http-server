<?php

namespace Websyspro;

class ClientAccept
{
  private ClientAcceptHeader $clientAcceptHeader;

  public function __construct(
    private HttpServer $HttpServer,
    private mixed $streamSocketAccept 
  ){
    $this->ready();
  }

  public function readyRequest(
  ): void {
    $this->clientAcceptHeader = new ClientAcceptHeader(
      $this->streamSocketAccept
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
      if ($this->HttpServer->isConnectionExceded()) {
        // TD para retorno de error Service Unavailable
        echo "Connection exceded\n";
      } else {
        $this->HttpServer->incrementConnection();
        $this->readyRequest();

        $json = json_encode([
          "message" => date("Y-m-d H:i:s")
        ]);

        $contentBody = json_encode(
          $this->clientAcceptHeader->propertys["ContentBody"]
        );

        $response  = "HTTP/1.1 200\r\n";
        $response .= "Content-Type: application/json\r\n";
        $response .= "Content-Length: " . strlen($contentBody) . "\r\n";
        $response .= "Connection: close\r\n\r\n";
        $response .= $contentBody;

        fwrite($this->streamSocketAccept, $response);
        $this->closeRequest();
        $this->HttpServer->decrementConnection();
      }
    }
  }
}