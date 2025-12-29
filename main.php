<?php

use Websyspro\Decorations\Server\AllowAnonymous;
use Websyspro\Decorations\Server\FileValidade;
use Websyspro\Decorations\Server\Authenticate;
use Websyspro\Decorations\Server\Controller;
use Websyspro\Decorations\Server\Module;
use Websyspro\Decorations\Server\Post;
use Websyspro\Decorations\Server\Body;
use Websyspro\Decorations\Server\Get;
use Websyspro\HttpServer;

#[Controller("user")]
#[Authenticate()]
class UserController
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
}

#[Controller("perfils")]
#[Authenticate()]
#[FileValidade()]
class PerfilController
{
  public function __construct(
  ){}

  #[Get("list/get/{productId}")]
  public function all(
  ): array {
    return [];
  }

  #[Post("list/products")]
  public function products(    
  ): array {
    return [ "key" => "Exmplo de produtos" ];
  }
  
  #[Post("list/find/products")]
  public function findProduct(
  ): array {
    return ["teste"];
  }
}

#[Module(
  controllers: [
    UserController::class,
    PerfilController::class
  ]
)]
class AccountsModule {}

$httpServer = new HttpServer();
$httpServer->factory( 
    [
        AccountsModule::class
        ]
    );
$httpServer->base( "api/v1" );
$httpServer->listen( 3002 );


