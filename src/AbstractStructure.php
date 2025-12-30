<?php

namespace Websyspro;

use Exception;
use ReflectionClass;
use ReflectionMethod;
use ReflectionAttribute;
use ReflectionParameter;
use ReflectionProperty;
use ReflectionType;
use Websyspro\Commons\Collection;
use Websyspro\Commons\Utils;
use Websyspro\Enums\ControllerType;
use Websyspro\Exceptions\Error;

abstract class AbstractStructure
{
  public Collection $attributes;
  public Collection $parameters;

  public function __construct(
    public ReflectionClass|ReflectionMethod $reflect
  ){
    $this->attributes = new Collection($reflect->getAttributes());
    if($this->reflect instanceof ReflectionMethod){
      $this->parameters = new Collection($reflect->getParameters());
    }

    $this->start();
  }

  public function start(
  ): void {}

  public function isValidMethod(
    ReflectionMethod $reflectionAttribute
  ): bool {
    return $reflectionAttribute->getName() !== "__construct";
  }

  public function structureRoute(
    ReflectionMethod $reflectionMethod
  ): StructureRoute {
    return new StructureRoute($reflectionMethod);
  }  
  
  public function createInstance(
    ReflectionAttribute|ReflectionParameter $reflectionAttribute
  ): mixed {
    return $reflectionAttribute->newInstance();
  }  

  public function isControllerType(
    ReflectionAttribute $reflectionAttribute,
    ControllerType $controllerType
  ): bool {
    return $this->createInstance($reflectionAttribute)->controllerType === $controllerType;
  }

  public function getMiddlewares(
  ): Collection {
    return $this->attributes
      ->where(fn(ReflectionAttribute $reflectionAttribute) => $this->isControllerType($reflectionAttribute, ControllerType::Middleware))
      ->mapper(fn(ReflectionAttribute $reflectionAttribute) => $this->createInstance($reflectionAttribute));
  }

  public function getMethods(
  ): Collection {
    return new Collection(
      $this->reflect->getMethods()
    );
  }
  
  public function getParameters(
  ): Collection {
    try {
      $parameters = $this->parameters
        ->mapper(function(ReflectionParameter $reflectionParameter) {
          if( Utils::sizeArray($reflectionParameter->getAttributes()) === 0 ){
            Error::internalServerError(
              "The parameter {$reflectionParameter->getName()} does not have an attribute."
            );
          }

          [ $reflectionAttribute ] = $reflectionParameter->getAttributes();

          if( $reflectionAttribute === null ){
            Error::internalServerError(
              "The parameter {$reflectionParameter->getName()} does not have an attribute."
            );
          }

          return new StructureParam(
            $reflectionAttribute->newInstance(),
            $reflectionParameter->getType()->getName()
          );
        });

      return $parameters;
    } catch (Exception $error) {
      Error::internalServerError(
        $error->getMessage()
      );
    }

    return new Collection();
  }  
}