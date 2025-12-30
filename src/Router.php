<?php

namespace Websyspro;

use ReflectionClass;
use Websyspro\InstanceDependences;
use Websyspro\Commons\Collection;
use Websyspro\Enums\MethodType;

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

  public function execute(
    Response $response,
    Request $request  
  ): void {
    if( is_array($this->fn) ){
      [ $this->class, $this->name 
      ] = $this->fn;

      $this->getMiddlewares()->mapper(
        fn(object $middleware): mixed => (
          $middleware->execute(
            $request
          )
        )
      );

      $response->status( 200 )->json(
        call_user_func_array(
          [ 
            InstanceDependences::getInstance(
              $this->class
            ), $this->name 
          ], $this->getParameters( $request )->all()
        )
      );
    } else if( is_callable( $this->fn )){
      call_user_func( 
        $this->fn, ...[ 
          $response, $request
        ]
      );
    }
  }
}