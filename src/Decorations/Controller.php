<?php

namespace Websyspro\Decorations;

use Websyspro\Enums\ControllerType;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Controller
{
  public ControllerType $controllerType = ControllerType::Controller;

  public function __construct(
    public string $name
  ){}
}