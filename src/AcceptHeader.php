<?php

namespace Websyspro;

use Websyspro\Commons\Utils;

class AcceptHeader
extends UtilsHeader
{
  private array $requestInvalids = [
    ".well-known/appspecific/com.chrome.devtools.json"
  ];

  public function getProtocolVersion(
  ): string|null {
    return $this->getHeader( "ProtocolVersion" );
  }

  public function getRequestMethod(): string|null {
    return $this->getHeader( "RequestMethod" );
  }

  public function getRequestUrlFull(): string|null {
    if($this->getHeader( "RequestUrl" ) === null){
      return null;
    }

    return preg_replace(
      "#(^/)|(/$)#",
      "",
      $this->getHeader( "RequestUrl" )
    );
  }  

  public function getRequestUrl(): string|null {
    if($this->getHeader( "RequestUrl" ) === null){
      return null;
    }

    [ $requestUrl ] = preg_split(
      "#\?#", $this->getHeader( "RequestUrl" )
    );

    return preg_replace(
      "#(^/)|(/$)#",
      "",
      $requestUrl
    );
  }

  private function isRequestQuery(
  ) : bool {
    return preg_match(
      "#\?#", 
      $this->getHeader( "RequestUrl" )
    ) === 1;
  }
    
  
  public function getRequestQuery(): string|null {
    if($this->getHeader( "RequestUrl" ) === null){
      return null;
    }

    if($this->isRequestQuery() === false){
      return null;
    }

    [ $_, $requestQuery ] = preg_split(
      "#\?#", $this->getHeader( "RequestUrl" )
    );

    return $requestQuery;
  }   

  public function getContentType(
  ): string|null {
    return $this->getHeader( "ContentType" );
  }

  public function getContentBody(
  ): string|null {
    return $this->getHeader( "ContentBody" );
  }

  private function hasBlackListeRequest(
  ): bool {
    return in_array($this->getRequestUrl(), $this->requestInvalids) ? false : true;
  }
  
  public function isContent(): bool {
    if(in_array($this->getRequestUrl(), $this->requestInvalids)){
      return false;
    }

    return Utils::sizeArray(
      $this->getPropertys()
    ) !== 0 || $this->hasBlackListeRequest();
  }
}