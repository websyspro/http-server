<?php

namespace Websyspro;

use Websyspro\Commons\Collection;
use Websyspro\Enums\MethodType;

class Router
{
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

  public function execute(
    Response $response,
    Request $request  
  ): void {
    if(is_callable( $this->fn )){
      call_user_func( $this->fn, ...[ $response, $request ]);
    }
  }
}