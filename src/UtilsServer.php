<?php

namespace Websyspro;

use Websyspro\Logger\Enums\LogType;
use Websyspro\Logger\Log;
use Exception;
use ReflectionAttribute;
use ReflectionClass;
use Websyspro\Commons\Collection;
use Websyspro\Commons\Utils;
use Websyspro\Decorations\Server\Module;
use Websyspro\Enums\MethodType;

abstract class UtilsServer
{
  private array $modules;
  private string $base;
  private int $port;
  private bool $running = true;
  private mixed $streamSocket;
  private string|null $errno = null;
  private string|null $error = null;
  private int $socketConnections = 0;
  private int $socketMaxConnections = 500;

  private Collection $routers;

  public function __construct(
  ){
    $this->routers = new Collection([]);
  }

  private function streamSetBlocking(
  ): void {
    @stream_set_blocking(
      $this->streamSocket,
      false
    );
  }

  private function httpServer(
  ): UtilsServer {
    return $this;
  }
  
  private function streamSocketServer(
  ): mixed {
    return stream_socket_server(
      "tcp://0.0.0.0:{$this->port}", 
      $this->errno,
      $this->error
    );    
  }

  private function streamSocketAccept(
  ): mixed {
    return @stream_socket_accept(
      $this->streamSocket, 1
    );
  }

  public function isMaxExceded(
  ): bool {
    return $this->socketConnections >= $this->socketMaxConnections;
  }

  public function incrementConnection(
  ): void {
    $this->socketConnections++;
  }

  public function decrementConnection(
  ): void {
    $this->socketConnections--;
  }

  private function createFork(
  ): int|null  {
    if(function_exists( "pcntl_fork")) {
      return (int)pcntl_fork();
    } else return null;
  } 

  private function createClient(
    HttpServer $httpServer,
    mixed $streamSocketAccept
  ): void {
    new AcceptClient(
      $httpServer, 
      $streamSocketAccept
    );
  }

  private function startLoop(
  ) {
    Log::message(
      LogType::service, 
      "Server started on port {$this->port}"
    );

    while($this->running){
      try {
        [ $httpServer, $streamSocketAccept ] = [
          $this->httpServer(), $this->streamSocketAccept()
        ];
        
        if( $streamSocketAccept ){
          $fork = $this->createFork();
          
          if(Utils::isNull( $fork )){
            $this->createClient(
              $httpServer, 
              $streamSocketAccept
            );
          } else {
            $this->createClient(
              $httpServer,
              $streamSocketAccept
            );

            if($fork === 0){
              exit();
            }
          }
        }
      } catch (Exception $error) {
        throw new Exception(
          $error
        );
      }
    }
  }

  private function start(
  ): void {
    $this->streamSocket = $this->streamSocketServer();
    if ($this->streamSocket === false) {
      throw new Exception(
        "Error: {$this->errno} - {$this->error}"
      );
    }

    $this->streamSetBlocking();
    $this->startLoop();
  }

  public function shutdown(
  ): void {
    Log::message(
      LogType::service,
      "Shutdown iniciado"
    );

    $this->running = false;
    if(is_resource($this->streamSocket)){
      fclose($this->streamSocket);
    }
  }

  private function startShutdown(
  ): void {
    if( function_exists( "pcntl_async_signals" )){
      if( function_exists( "pcntl_signal" )){
        if( defined( "SIGTERM" ) && defined( "SIGINT" )){
          pcntl_async_signals(true);

          pcntl_signal(SIGTERM, fn() => $this->shutdown());
          pcntl_signal(SIGINT, fn() => $this->shutdown());
        }
      }
    }
  }

  public function getRouters(
  ): Collection {
    return $this->routers;
  }

  public function addEndPoint(
    MethodType $requestMethod,
    string $requestPath, callable|array $fn
  ): void {
    $this->routers->add(
      new Router(
        $requestMethod, 
        preg_replace(
          "#^/#", 
          "", 
          "{$this->getBase()}/{$requestPath}"
        ), $fn
      )
    );
  }  

  public function setModules(
    array $modules
  ): void {
    $this->modules = $modules;
  }

  public function getModules(
  ): Collection {
    return new Collection(
      $this->modules
    );
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
      sprintf("Map Route %s %s", ...[
        $structureRoute->methodType->name,
        $structureRoute->getEndPoint()
      ])
    );

    $this->addEndPoint( 
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
  ): void {
    $this->getModules()->mapper(
      fn(string $module) => (
        $this->factoryReadyModule(
          $module
        )
      )
    );
  }

  public function startFactory(
  ): void {
    $this->factoryReady();
  }  

  public function getBase(
  ): string|null {
    return $this->base ?? null;
  }  

  public function base(
    string $base
  ): void {
    $this->base = $base;
  } 

  public function listen(
    int $port
  ): void {
    $this->port = $port;
    $this->startShutdown();
    $this->startFactory();
    $this->start();
  }
}