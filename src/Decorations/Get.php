<?php

namespace Websyspro\Decorations;

use Attribute;
use Websyspro\Enums\ControllerType;
use Websyspro\Enums\MethodType;
use Websyspro\Decorations\AbstractEndpoint;

#[Attribute(Attribute::TARGET_METHOD)]
class Get extends AbstractEndpoint
{
  public MethodType $methodType = MethodType::GET;
  public ControllerType $controllerType = ControllerType::Endpoint;
}