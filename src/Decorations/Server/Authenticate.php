<?php

namespace Websyspro\Decorations\Server;

use Websyspro\Enums\ControllerType;
use Websyspro\Exceptions\Error;
use Websyspro\Request;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Authenticate
{
  public ControllerType $controllerType = ControllerType::Middleware;

  public function __construct(
  ){}

  public function execute(
    Request $request
  ): void {}  
}