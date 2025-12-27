<?php

namespace Websyspro;

class AcceptHeader
{
  public array $propertys = [];
  private int $maxPacketSize = 8192;
  private int $packetsReceived = 0;

  public function __construct(
    public mixed $streamSocketAccept
  ){
    $this->ready();
  }

  public function normalizeProperty(
    string $property
  ): string {
    return preg_replace(
      "#-#",
      "", 
      $property
    );
  }

  private function removeBreak(
    string|null $value = null
  ): string|null {
    if (is_null($value)) {
      return null;
    }

    return preg_replace(
      "#(^\r\n)|(\r\n$)#",
      "", 
      $value
    );
  }

  private function readyParse(
    string $row
  ): void {
    if ((bool)preg_match(
      "#^(GET|POST|PUT|PATCH|DELETE|HEAD|OPTIONS)#", 
      $row
    ) === true){
      [ $method, $requestUrl, $protocolVersion ] = preg_split(
        '/\s+/', $row
      );

      $this->propertys["Method"] = $method;
      $this->propertys["RequestUrl"] = $requestUrl;
      $this->propertys["ProtocolVersion"] = $protocolVersion;
    } else {
      [ $property, $value ] = preg_split(
        '/:\s+/', $row
      );

      $this->propertys[
        $this->normalizeProperty( $property )
      ] = $this->removeBreak( $value );
    }
  }

  private function bodySize(
  ): int {
    return (int)(
      isset($this->propertys["ContentLength"]) 
        ? $this->propertys["ContentLength"]
        : 0
    );
  } 

  private function packetsReceivedSize(
  ): int {
    return $this->packetsReceived;
  }

  private function readyBody(
  ): void {
    while ($this->packetsReceivedSize() < $this->bodySize()) {
      $chunkReceived = fread( $this->streamSocketAccept, min(
        $this->maxPacketSize, (int)(
          $this->bodySize() - $this->packetsReceivedSize()
        )
      ));

      if ($chunkReceived === false) {
        break;
      }

      $this->propertys["ContentBody"] .= $chunkReceived;
      $this->packetsReceived += strlen( $chunkReceived );
    }
  }

  private function ready(
  ): void {
    while (($row = fgets($this->streamSocketAccept)) !== false) {
      if ((bool)preg_match( "#^\s*$#", $row ) === false) {
        $this->readyParse( $row );
      }

      if ($row === "\r\n") {
        break;
      }
    }

    $this->readyBody();
  }

  public function protocolVersion(
  ): string {
    return $this->propertys["ProtocolVersion"];
  }  

  public function method(
  ): string {
    return $this->propertys["Method"];
  }

  public function requestUrl(
  ): string {
    return $this->propertys["RequestUrl"];
  }

  public function contentType(
  ): string {
    return $this->propertys["ContentType"];
  }   

  public function contentBody(
  ): string {
    return $this->propertys["ContentBody"];
  }  
}