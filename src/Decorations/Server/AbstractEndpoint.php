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
    public string $descriptor
  ){
    $this->descriptor = preg_replace(
      "#(^/)|(/$)#", "", $this->descriptor
    );
  }

  public function getEndpoints(
  ): Collection {
    return new Collection(
      preg_split("#/#", $this->descriptor)
    );
  }

  public function getMethodType(
  ): MethodType {
    return $this->methodType;
  }   
}