<?php

namespace Websyspro\Decorations\Server;

use Websyspro\Commons\Utils;

abstract class AbstractParameter
{
  public function getValue(
    mixed $dataValue,
    string $instanceType,
    string|null $key = null
  ): mixed {
    if(is_array($dataValue)){
      if(is_null($key) === false){
        if(isset($dataValue[$key])){
          if(Utils::isPrimitiveType($instanceType)){
            return $dataValue[$key];
          } else {
            return Utils::hydrateObject(
              $dataValue[$key], $instanceType
            );
          }
        } else return null;
      }

      if(Utils::isPrimitiveType($instanceType)){
        return $dataValue;
      } else return Utils::hydrateObject(
        $dataValue, $instanceType
      );
    }
    
    return $dataValue;
  }
}