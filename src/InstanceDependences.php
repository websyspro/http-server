<?php

namespace Websyspro;

use ReflectionClass;
use ReflectionParameter;
use Websyspro\Commons\Utils;

class InstanceDependences
{
  public static function getInstance(
    string|object $class
  ): object {
    $hasConstruct = method_exists(
      $class, "__construct"
    );

    if($hasConstruct === true){
      return InstanceDependences::gets($class);
    } else return new $class;
  }

  public static function gets(
    string $objectClass
  ): object {
    $reflectionClass = (
      new ReflectionClass(
        $objectClass
      )
    );

    if( $reflectionClass ){
      if($reflectionClass->getConstructor()){
        $getParameters = (
          $reflectionClass
            ->getConstructor()
            ->getParameters()
        );
      }

      if( $getParameters ){
        $getParametersList = Utils::mapper(
          $getParameters, (
            function( ReflectionParameter $reflectionParameter ) {
              if( $reflectionParameter->isDefaultValueAvailable() === false ){
                return InstanceDependences::gets(
                  $reflectionParameter->getType()->getName()
                );
              }

              return $reflectionParameter->getDefaultValue();
            }
          )
        );

        return call_user_func_array([
          new ReflectionClass(
            $objectClass
          ), "newInstance"
        ], $getParametersList );
      }

      return new $objectClass();
    }

    return new $objectClass();
  }
}