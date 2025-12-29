<?php

namespace Websyspro\Core\Decorations\Server;

use Attribute;
use Websyspro\Enums\ControllerType;
use Websyspro\Enums\MethodType;
use WebsysproDecorations\AbstractEndpoint;

#[Attribute(Attribute::TARGET_METHOD)]
class Get extends AbstractEndpoint
{
  public MethodType $methodType = MethodType::GET;
  public ControllerType $controllerType = ControllerType::Endpoint;
}