<?php

namespace Websyspro\Decorations;

use Websyspro\Decorations\AbstractEndpoint;
use Websyspro\Enums\ControllerType;
use Websyspro\Enums\MethodType;
use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Put extends AbstractEndpoint
{
  public MethodType $methodType = MethodType::PUT;
  public ControllerType $controllerType = ControllerType::Endpoint;
}