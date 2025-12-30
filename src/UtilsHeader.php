<?php

namespace Websyspro;

use Websyspro\Commons\Utils;

abstract class UtilsHeader
{
  private array $propertys = [];
  private int $maxPacketSize = 8192;
  private int $packetsReceived = 0;

  public function __construct(
    private mixed $streamSocketAccept
  ){
    $this->setReady();
  }

  private function getBodySize(
  ): int {
    return (int)(
      Utils::existVar($this->propertys["ContentLength"]) 
        ? $this->propertys["ContentLength"] : 0
    );
  } 

  private function getPacketsReceivedSize(
  ): int {
    return $this->packetsReceived;
  }  

  public function setNormalizeProperty(
    string $property
  ): string {
    return preg_replace(
      "#-#",
      "", 
      $property
    );
  }

  private function setRemoveBreak(
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
  
  private function setReadyHeaderParse(
    string $header
  ): void {
    if ((bool)preg_match(
      "#^(GET|POST|PUT|PATCH|DELETE|HEAD|OPTIONS)#", 
      $header
    ) === true){
      [ $method, $requestUrl, $protocolVersion ] = preg_split(
        '/\s+/', $header
      );

      $this->propertys["RequestMethod"] = $method;
      $this->propertys["RequestUrl"] = $requestUrl;
      $this->propertys["ProtocolVersion"] = $protocolVersion;
    } else {
      [ $property, $value ] = preg_split(
        '/:\s+/', $header
      );

      $this->propertys[
        $this->setNormalizeProperty( $property )
      ] = $this->setRemoveBreak( $value );
    }
  }

  private function setReadyBody(
  ): void {
    while ($this->getPacketsReceivedSize() < $this->getBodySize()) {
      $chunkReceived = fread( $this->streamSocketAccept, min(
        $this->maxPacketSize, (int)(
          $this->getBodySize() - $this->getPacketsReceivedSize()
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
  
  private function isEstablished(
  ): bool {
    $read = [ $this->streamSocketAccept ];
    $write = $except = [];

    return stream_select(
      $read,
      $write, 
      $except,
      1
    ) > 0;
  }

  private function setReady(
  ): void {
    if($this->isEstablished()){
      $header = fgets(
        $this->streamSocketAccept
      );

      if( $header ) {
        $this->setReadyHeaderParse(
          $header
        );

        while(($header = fgets($this->streamSocketAccept)) !== false){
          if((bool)preg_match( "#^\s*$#", $header ) === false){
            $this->setReadyHeaderParse( $header );
          }

          if($header === "\r\n"){
            break;
          }
        }

        $this->setReadyBody();
      }
    }
  }

  public function getHeader(
    string $header
  ): string|null {
    return isset(
      $this->propertys[ $header ]
    ) ? $this->propertys[ $header ] : null;
  }
}