<?php

namespace Websyspro;

class UtilFormData
{
  public function getValue(
    string $pattern,
    string $subject
  ): string {
    return preg_replace(
      $pattern,
      "$1", 
      $subject
    );
  }
}