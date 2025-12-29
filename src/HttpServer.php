<?php

namespace Websyspro;

use Websyspro\Decorations\Server\Module;
use Websyspro\Commons\Collection;
use Websyspro\Enums\MethodType;
use ReflectionAttribute;
use ReflectionClass;
use Websyspro\Logger\Enums\LogType;
use Websyspro\Logger\Log;

class HttpServer 
extends UtilServer
{
  public function __construct(
    private Collection $routers = new Collection([])
  ){}
  
  public function getRouters(
  ): Collection {
    return $this->routers;
  }
  private function add(
    MethodType $requestMethod,
    string $requestPath, callable|array $fn
  ): void {
    $this->routers->add(
      new Router(
        $requestMethod, 
        $requestPath, $fn
      )
    );
  }

  public function get(
    string $path,
    callable|null $fn = null
  ) : void {
    $this->add( MethodType::GET, $path, $fn );
  }

  public function post(
    string $path,
    callable|null $fn = null
  ) : void {
    $this->add( MethodType::POST, $path, $fn );
  }

  public function put(
    string $path,
    callable|null $fn = null
  ) : void {
    $this->add( MethodType::PUT, $path, $fn );
  }

  public function patch(
    string $path,
    callable|null $fn = null
  ) : void {
    $this->add( MethodType::PATCH, $path, $fn );
  }

  public function delete(
    string $path,
    callable|null $fn = null
  ) : void {
    $this->add( MethodType::DELETE, $path, $fn );
  }

  public function head(
    string $path,
    callable|null $fn = null
  ) : void {
    $this->add( MethodType::HEAD, $path, $fn );
  }

  public function options(
    string $path,
    callable|null $fn = null
  ) : void {
    $this->add( MethodType::OPTIONS, $path, $fn );
  }

  private function getPrefixFromClass(
    string $pattern,
    string $class
  ): string {
    return preg_replace(
      $pattern,
      "", 
      strtolower(
        $class
      )
    );   
  }

  private function factoryReadyEndpointsControllerInModule(
    StructureRoute $structureRoute,
    string $module
  ): void {
    Log::message(
      LogType::controller,
      sprintf("Map Route {%s, %s}", ...[
        $structureRoute->methodType->name,
        $structureRoute->getEndPoint()
      ])
    );

    $this->add( 
      $structureRoute->methodType, 
      sprintf(
        "%s/%s/%s", ...[
          $this->getPrefixFromClass(
            "#(Module)|(module)$#",
            $module
          ),
          $this->getPrefixFromClass(
            "#(Controller)|(controller)$#", 
            $structureRoute->reflect->class
          ), $structureRoute->getEndPoint()
        ]
      ), [ $structureRoute->reflect->class, $structureRoute->reflect->name ]
    );    
  }

  private function factoryReadyControllerInModule(
    StructureController $structureController,
    string $module
  ): void {
    Log::message(
      LogType::controller, 
      "Map Controllers {$structureController->reflect->name}"
    );

    $structureController->endpoints->mapper(
      fn( StructureRoute $structureRoute ) => (
        $this->factoryReadyEndpointsControllerInModule(
          $structureRoute, $module
        )
      )
    );
  }

  private function factoryReadyModule(
    string $module,
  ): void {
    [ $reflectAttribute ] = new ReflectionClass(
      $module
    )->getAttributes();

    if( $reflectAttribute instanceof ReflectionAttribute ){
      $moduleFromInstance = $reflectAttribute->newInstance();

      Log::message(
        LogType::module, 
        "Map Module [{$module}]"
      );

      if($moduleFromInstance instanceof Module){
        $controllersFromModule = new Collection( 
          $moduleFromInstance->controllers
        );

        $controllersFromModule->mapper(
          fn(string $controller) => (
            $this->factoryReadyControllerInModule(
              new StructureController(
                new ReflectionClass( $controller)
              ), $module
            )
          )
        );
      }
    }
  }

  private function factoryReady(
    Collection $modules,
  ): void {
    $modules->mapper(
      fn(string $module) => (
        $this->factoryReadyModule(
          $module
        )
      )
    );
  }
  
  public function factory(
    array $modules = []
  ): void {
    $this->factoryReady(
      new Collection(
        $modules
      )
    );
  }  
}
