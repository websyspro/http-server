<?php

use Websyspro\Decorations\Server\AllowAnonymous;
use Websyspro\Decorations\Server\Authenticate;
use Websyspro\Decorations\Server\Controller;
use Websyspro\Decorations\Server\Module;
use Websyspro\Decorations\Server\Post;
use Websyspro\Decorations\Server\Body;
use Websyspro\Decorations\Server\Get;
use Websyspro\Decorations\Server\Query;
use Websyspro\HttpServer;
use Websyspro\Request;
use Websyspro\Response;

#[Controller("test")]
#[Authenticate()]
class TestController
{
  public function __construct(
  ){}

  #[Post("{testId:bool}/details")]
  #[AllowAnonymous()]
  public function all(  
    #[Body()] array $testId
  ): array {
    return [];
  }

  #[Get("timer")]
  public function timer(  
    #[Query()] array $query
  ): array {
    return [];
  }

  #[Get("sleep")]
  public function sleep(  
  ): int {
    return 1;
  }  
}


#[Module(
  controllers: [
    TestController::class,
  ]
)]
class BaseModule {}

$httpServer = new HttpServer();
$httpServer->base( "api/v1" );
$httpServer->factory( 
  [
    BaseModule::class
  ]
);

$httpServer->listen( 3002 );


