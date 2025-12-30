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
    public string $route
  ){
    $this->route = preg_replace(
      "#(^/)|(/$)#", "", $this->route
    );
  }

  public function getRoute(
  ): Collection {
    return new Collection(
      preg_split(
        "#/#",
        $this->route
      )
    );
  }

  public function getMethodType(
  ): MethodType {
    return $this->methodType;
  }   
}