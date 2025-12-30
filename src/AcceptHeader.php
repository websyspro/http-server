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

  private function isQueryInUrl(): bool {
    if (is_null($this->requestUrl())) {
      return false;
    }

    return preg_match(
      "#\?#", $this->requestUrl()
    ) === 1;
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

      $this->propertys["ContentBody"] = "";
      $this->propertys["ContentBody"].= $chunkReceived;
      $this->packetsReceived += strlen( $chunkReceived );
    }
  }

  private function ready(
  ): void {
    $header = fgets( $this->streamSocketAccept);
    if( $header ) {
      $this->readyParse( $header );

      while (($header = fgets($this->streamSocketAccept)) !== false) {
        if ((bool)preg_match( "#^\s*$#", $header ) === false) {
          $this->readyParse( $header );
        }

        if ($header === "\r\n") {
          break;
        }
      }

      $this->readyBody();
    }
  }

  public function protocolVersion(
  ): string|null {
    return $this->propertys["ProtocolVersion"] ?? null;
  }  

  public function requestMethod(
  ): string|null {
    return $this->propertys["Method"] ?? null;
  }

  public function requestUrl(
  ): string|null {
    if(isset( $this->propertys["RequestUrl"] ) === false) {
      return null;
    }

    [ $requestUrl ] = preg_split(
      "#\?#",
      $this->propertys["RequestUrl"]
    );

    return $requestUrl;
  }

  public function requestUrlSeparator(
  ): string|null {
    return $this->isQueryInUrl() ? "?" : "";
  }  

  public function contentType(
  ): string|null {
    return $this->propertys["ContentType"] ?? null;
  }   

  public function contentBody(
  ): string|null {
    return $this->propertys["ContentBody"] ?? null;
  }

  public function requestUrlQuery(
  ): string|null {
    if(isset( $this->propertys["RequestUrl"] ) === false) {
      return null;
    }

    if($this->isQueryInUrl() === false) {
      return null;
    }

    [ $_, $query ] = preg_split(
      "#\?#",
      $this->propertys["RequestUrl"]
    );

    return $query;
  }  
}