<?php

namespace Websyspro\Decorations;

use Attribute;
use Websyspro\Core\Commons\Collection;

#[Attribute(Attribute::TARGET_CLASS)]
class Module
{
  public function __construct(
    public readonly array $controllers
  ){}
}