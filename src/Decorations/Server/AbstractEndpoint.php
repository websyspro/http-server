<?php

namespace Websyspro\Decorations\Server;

use Websyspro\Enums\ControllerType;
use Websyspro\Commons\Collection;
use Websyspro\Enums\MethodType;

abstract class AbstractEndpoint
{
  public MethodType $methodType = MethodType::GET;
  public ControllerType $controllerType = ControllerType::Endpoint;

  public function __construct(
    public string $url
  ){
    $this->url = preg_replace(
      "#(^/)|(/$)#", "", $this->url
    );
  }

  public function getUrl(
  ): Collection {
    return new Collection(
      preg_split(
        "#/#",
        $this->url
      )
    );
  }

  public function getMethodType(
  ): MethodType {
    return $this->methodType;
  }   
}