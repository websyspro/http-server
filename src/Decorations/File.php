<?php

namespace Websyspro\Decorations;

use Websyspro\Enums\ControllerType;
use Websyspro\Request;
use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class File extends AbstractParameter
{
  public ControllerType $controllerType = ControllerType::Parameter;

  public function __construct(
    public readonly string|null $key = null
  ){}
  
  public function execute(
    Request $request,
    string $instanceType
  ): mixed {
    return $this->getValue(
      $request->files, 
      $instanceType, 
      $this->key
    );
  }
}