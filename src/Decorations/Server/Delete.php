<?php

namespace Websyspro\Decorations\Server;

use Websyspro\Enums\ControllerType;
use Websyspro\Enums\MethodType;
use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Delete extends AbstractEndpoint
{
  public MethodType $methodType = MethodType::DELETE;
  public ControllerType $controllerType = ControllerType::Endpoint;
}