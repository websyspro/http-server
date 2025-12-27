<?php

namespace Websyspro;

use Websyspro\Enums\RequestMethod;

class Router
{
  public function __construct(
    private RequestMethod $requestMethod,
    private string $path,
    private mixed $fn
  ){}

  public function getPath(
  ): string {
    return $this->path;
  }

  public function equalRequestMethod(
    RequestMethod $requestMethod
  ): bool {
    return $this->requestMethod === $requestMethod;
  }

  public function equalPath(
    string $path
  ): bool {
    return $this->path === $path;
  }

  public function valid(
    string $requestMethod,
    string $path
  ): bool {
    return $this->requestMethod->value === $requestMethod;
  }

  public function execute(
    Response $response,
    Request $request  
  ): void {
    if(is_callable( $this->fn )){
      call_user_func( $this->fn, ...[ $response, $request ]);
    }
  }
}