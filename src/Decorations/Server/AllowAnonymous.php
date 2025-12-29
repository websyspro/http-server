<?php

namespace Websyspro\Decorations\Server;

use Websyspro\Enums\ControllerType;
use Websyspro\Request;
use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class AllowAnonymous
{
  public ControllerType $controllerType = ControllerType::Middleware;

  public function execute(
    Request $request
  ): void {}
}