<?php

namespace Websyspro\Decorations\Server;

use Websyspro\Enums\ControllerType;
use Websyspro\Exceptions\Error;
use Websyspro\Commons\Utils;

abstract class AbstractParameter
{
  public ControllerType $controllerType = ControllerType::Parameter;

  public function getValue(
    array|object|null $value,
    string $instanceType,
    string|null $key = null
  ): mixed {
    $valueType = Utils::getType(
      $value
    );

    if( Utils::isNull( $value )){
      Error::internalServerError( 
        "Attributo [{$this->controllerType->name}] is null"
      );      
    }

    if( $valueType !== $instanceType ){
      Error::internalServerError( 
        "Attributes [{$this->controllerType->name}] with incompatible types, received {} expected {$instanceType}"
      );       
    }
    

    if( Utils::isArray( $value )){
      if( Utils::sizeArray( $value ) === 0 && Utils::isNull( $key )){
        Error::internalServerError( 
          "Attributo [{$this->controllerType->name}]({$key})) is not exists"
        );
      }

      /*
       * Verificar instanceType is Primitivo
       * */
      if( Utils::isPrimitiveType( $instanceType )){
        return Utils::isNull( $key ) ? $value : $value[ $key ];
      } else {
        return Utils::isNull( $key ) 
          ? Utils::hydrateObject( $value, $instanceType )
          : Utils::hydrateObject( $value[ $key ], $instanceType );
      }
    }

    if( Utils::isObject( $value )){
      if( Utils::isObjectEmpty( $value ) && Utils::isNull( $key )){
        Error::internalServerError( 
          "Attributo [{$this->controllerType->name}]({$key})) is not exists"
        );
      }

      /*
       * Verificar instanceType is Primitivo
       * */
      if( Utils::isPrimitiveType( $instanceType )){
        return Utils::isNull( $key ) ? $value : $value[ $key ];
      } else {
        return Utils::isNull( $key ) 
            ? Utils::hydrateObject( $value, $instanceType )
            : Utils::hydrateObject( $value[$key], $instanceType );
      }
    }

    return null;
  }
}