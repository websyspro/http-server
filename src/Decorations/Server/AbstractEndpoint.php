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
    public string $endpoint
  ){
    $this->endpoint = preg_replace(
      "#(^/)|(/$)#", "", $this->endpoint
    );
  }

  public function getEndpoints(
  ): Collection {
    return new Collection(
      preg_split(
        "#/#",
        $this->endpoint
      )
    );
  }

  public function getMethodType(
  ): MethodType {
    return $this->methodType;
  }   
}