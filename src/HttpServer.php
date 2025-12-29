<?php

namespace Websyspro;

use Websyspro\Decorations\Server\Module;
use Websyspro\Logger\Enums\LogType;
use Websyspro\Commons\Collection;
use Websyspro\Enums\MethodType;
use Websyspro\Logger\Log;
use ReflectionAttribute;
use ReflectionClass;

class HttpServer 
extends UtilServer
{
  public function get(
    string $path,
    callable|null $fn = null
  ) : void {
    $this->addEndPoint( MethodType::GET, $path, $fn );
  }

  public function post(
    string $path,
    callable|null $fn = null
  ) : void {
    $this->addEndPoint( MethodType::POST, $path, $fn );
  }

  public function put(
    string $path,
    callable|null $fn = null
  ) : void {
    $this->addEndPoint( MethodType::PUT, $path, $fn );
  }

  public function patch(
    string $path,
    callable|null $fn = null
  ) : void {
    $this->addEndPoint( MethodType::PATCH, $path, $fn );
  }

  public function delete(
    string $path,
    callable|null $fn = null
  ) : void {
    $this->addEndPoint( MethodType::DELETE, $path, $fn );
  }

  public function head(
    string $path,
    callable|null $fn = null
  ) : void {
    $this->addEndPoint( MethodType::HEAD, $path, $fn );
  }

  public function options(
    string $path,
    callable|null $fn = null
  ) : void {
    $this->addEndPoint( MethodType::OPTIONS, $path, $fn );
  }

  public function factory(
    array $modules = []
  ): void {
    $this->setModules(
      $modules
    );
  }  
}
