<?php

namespace Websyspro\Decorations\Server;


use Websyspro\Enums\ControllerType;
use Websyspro\Enums\MethodType;
use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Get extends AbstractEndpoint
{
  public MethodType $methodType = MethodType::GET;
  public ControllerType $controllerType = ControllerType::Endpoint;
}