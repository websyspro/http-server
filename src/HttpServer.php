<?php

namespace Websyspro;

use Websyspro\Commons\Collection;
use Websyspro\Enums\MethodType;

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
    string $path, callable $fn
  ): void {
    $this->routers->add(
      new Router(
        $requestMethod, 
        $path, $fn
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
  
  public function factory(
  ): void {}  
}
