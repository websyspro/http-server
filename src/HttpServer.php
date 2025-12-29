<?php

namespace Websyspro;

use Websyspro\Commons\Collection;
use Websyspro\Enums\MethodType;

class HttpServer 
extends ExpressServer
{
  public function __construct(
    private Collection $routers = new Collection([])
  ){}

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
  
  public function getRouters(
  ): Collection {
    return $this->routers;
  }

  public function post(
    string $path,
    callable|null $fn = null
  ) : void {
    $this->add( MethodType::POST, $path, $fn );
  }

  public function get(
    string $path,
    callable|null $fn = null
  ) : void {
    $this->add( MethodType::GET, $path, $fn );
  }
  
  public function factory(
  ): void {}  
}
