<?php

namespace Websyspro;

class StructureParam
{
  public function __construct(
    public object $instance,
    public string $instanceType
  ){}
}