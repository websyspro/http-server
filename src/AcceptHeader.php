<?php

namespace Websyspro;

class AcceptHeader
extends UtilsHeader
{
  public function getProtocolVersion(
  ): string|null {
    return $this->getHeader( "ProtocolVersion" );
  }

  public function getRequestMethod(): string|null {
    return $this->getHeader( "RequestMethod" );
  }

  public function getRequestUrl(): string|null {
    return $this->getHeader( "RequestUrl" );
  }  

  public function getContentType(
  ): string|null {
    return $this->getHeader( "ContentType" );
  }

  public function getContentBody(
  ): string|null {
    return $this->getHeader( "ContentBody" );
  }  
}