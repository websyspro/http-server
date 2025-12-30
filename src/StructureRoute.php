<?php

namespace Websyspro;

use Websyspro\commons\Collection;
use Websyspro\Enums\ControllerType;
use Websyspro\Enums\MethodType;
use ReflectionAttribute;
use Websyspro\Request;

class StructureRoute
extends AbstractStructure
{
  public Collection $endpoints;
  public Collection $middlewares;
  public MethodType $methodType;

  public function start(
  ): void {
    $this->startEndpoint();
  }

  private function startEndpoint(
  ): void {
    $methodAttributes = $this->attributes
      ->where(fn(ReflectionAttribute $reflectionAttribute) => $this->isControllerType($reflectionAttribute, ControllerType::Endpoint))
      ->mapper(fn(ReflectionAttribute $reflectionAttribute) => $this->createInstance($reflectionAttribute));

    if($methodAttributes->exist()){
      $this->methodType = $methodAttributes->first()->getMethodType();
      $this->endpoints = $methodAttributes->first()->getRoute();
    }
  }

  public function getEndPoint(
  ): string {
    return $this->endpoints->join("/");
  }

  private function validRequestMethod(
    Request $request
  ): bool {
    return true;
    //return $this->requestMethod === $request->requestMethod;
  }

  private function validRequestPaths(
    Request $request
  ): bool {
    return true;
    /*
    $paths = $this->endpoints->mapper(
      function(string $path, int $i) use($request){
        $hasParams = (bool)preg_match(
          "#(^\{.*\}$)|(^\{.*\}\?$)|(^:.*)|(^:.*\?$)#", $path
        );

        if($hasParams === true){
          return $hasParams;
        }
        
        return $path === $request->endpoints->eq($i)->first();
      }
    );

    return $paths->where(
      fn(bool $val) => $val === false
    )->exist() === false; */
  }

  public function valid(
    Request $request
  ): bool {
    return $this->validRequestMethod($request)
        && $this->validRequestPaths($request);
  }  
}