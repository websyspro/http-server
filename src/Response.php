<?php

namespace Websyspro;

class Response
{
  private int $code = 200;

  public function __construct(
    public mixed $streamSocketAccept
  ){}
  
  private function data(
    string $contentType,
    mixed $contentData,
  ): string {
    return implode(
      "\r\n",
      [
        "HTTP/1.1 {$this->code}",
        "Content-Type: {$contentType}",
        "Content-Length: " . strlen( $contentData ),
        "Connection: close",
        "",
        "{$contentData}"
      ]
    );
  }

  public function status(
    int $code = 200
  ): Response {
    $this->code = $code;
    return $this;
  }

  public function json(
    mixed $value
  ): void {
    fwrite(
      $this->streamSocketAccept, 
      $this->data(
        "application/json",
        json_encode(
          $value
        )
      )
    );    
  }
}