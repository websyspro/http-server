<?php

namespace Websyspro\Decorations\Server;

use Websyspro\Enums\ControllerType;
use Websyspro\Enums\MethodType;
use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Patch extends AbstractEndpoint
{
  public MethodType $methodType = MethodType::PATCH;
  public ControllerType $controllerType = ControllerType::Endpoint;
}