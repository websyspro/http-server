<?php

namespace Websyspro;

use Exception;
use Websyspro\Commons\Collection;
use Websyspro\Commons\Utils;
use Websyspro\Enums\MethodType;
use Websyspro\Exceptions\Error;
use Websyspro\InstanceDI;
use ReflectionClass;

class Router
{
  private string $class;
  private string $name;

  private StructureController $structureController;
  private StructureRoute $structureRoute;

  public function __construct(
    private MethodType $requestMethod,
    private string $requestUrl,
    private mixed $fn
  ){}

  public function equalRequestMethod(
    string $requestMethod
  ): bool {
    return $this->requestMethod->name === $requestMethod;
  }

  private function createPaths(
    string|null $requestUrl = null
  ): Collection {
    $createPaths = new Collection(
      explode(
        "/", 
        $requestUrl ?? $this->requestUrl
      )
    );

    return $createPaths->where(
      fn(string $path) => empty($path) === false
    );
  }

  private function equalRequestUrlValid(
    Collection $requestUrlRouter,
    Collection $requestUrlHeader,
  ): bool {
    return $requestUrlRouter
      ->mapper(
        function(string $path, int $index) use($requestUrlHeader) {
          $hasParams = preg_match(
            "#(^\{.*\}$)|(^\{.*\}\?$)|(^:.*)|(^:.*\?$)#", 
            $path
          ) === 1;

          if($hasParams === true){
            return $hasParams;
          }
          
          return $path === $requestUrlHeader
            ->eq($index)->first();
        }
      )->where(fn(bool $val) => $val === false)->exist() === false;
  }

  public function equalRequestUrl(
    string $requestUrl
  ): bool {
    $requestUrlRouter = $this->createPaths();
    $requestUrlHeader = $this->createPaths( $requestUrl );
    
    if($requestUrlRouter->count() !== $requestUrlHeader->count()){
      return false;
    }

    return $this->equalRequestUrlValid(
      $requestUrlRouter,
      $requestUrlHeader
    );
  }

  public function isValid(
    string $requestMethod,
    string $requestUrl
  ): bool {
    return $this->equalRequestMethod( $requestMethod )
        && $this->equalRequestUrl( $requestUrl );
  }

  private function getMiddlewares(
  ): Collection {
    $this->structureController = new StructureController(
      new ReflectionClass(
        $this->class
      )
    );

    $this->structureRoute = $this->structureController->endpoints->find(
      fn(StructureRoute $structureRoute): bool => (
        $structureRoute->reflect->name === $this->name
      ) 
    );

    return $this->structureController
      ->getMiddlewares()
      ->where(fn( object $middleware ): bool => (
          ($middleware instanceof Authenticate) ? (
            $this->structureRoute->getMiddlewares()->where(
              fn(object $middleware) => (
                $middleware instanceof AllowAnonymous
              )
            )->exist() === false
          ) : true
        ))
      ->merge($this->structureRoute->getMiddlewares());
  }

  private function getParameters(
    Request $request
  ): Collection {
    return $this->structureRoute->getParameters()->mapper(fn(object $parameter): mixed => (
      $parameter->instance->execute( $request, $parameter->instanceType )
    ));
  }

  public function executeFromFactory(
    Response $response,
    Request $request  
  ): void {
    try {
      [ $this->class, $this->name 
      ] = $this->fn;

      $this->getMiddlewares()->mapper(
        fn(object $middleware): mixed => (
          $middleware->execute($request)
        )
      );

      $response->status( 200 )->json(
        call_user_func_array(
          [ 
            InstanceDI::getInstance(
              $this->class
            ), $this->name 
          ], $this->getParameters(
            $request
          )->all()
        )
      );
    } catch( Exception $error ){
      Error::internalServerError(
        $error->getMessage()
      );
    }
  }

  private function executeFromFN(
    Response $response,
    Request $request
  ): void {
    try {
      call_user_func( $this->fn, ...[ 
        $response, $request
      ]);
    } catch( Exception $error ){
      Error::internalServerError(
        $error->getMessage()
      );
    }
  }

  public function execute(
    Response $response,
    Request $request  
  ): void {
    Utils::isArray( $this->fn )
     ? $this->executeFromFactory( $response, $request )
     : $this->executeFromFN( $response, $request );
  }
}