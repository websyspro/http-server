<?php

namespace Websyspro\Core\Decorations\Server;

use Websyspro\Enums\ControllerType;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class FileValidade
{
  public ControllerType $controllerType = ControllerType::Middleware;

  public function __construct(
  ){}
}