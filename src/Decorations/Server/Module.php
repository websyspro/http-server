<?php

namespace Websyspro\Decorations\Server;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Module
{
  public function __construct(
    public readonly array $controllers
  ){}
}