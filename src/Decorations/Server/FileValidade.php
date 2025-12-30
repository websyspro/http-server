<?php

namespace Websyspro\Decorations\Server;

use Websyspro\Enums\ControllerType;
use Websyspro\Request;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class FileValidade
{
  public ControllerType $controllerType = ControllerType::Middleware;

  public function __construct(
  ){}

  public function execute(
    Request $request
  ): void {}    
}