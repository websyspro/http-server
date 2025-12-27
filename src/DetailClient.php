<?php

namespace Websyspro;

class DetailClient
{
  private string $ip;
  private string $port;

  public function __construct(
    public mixed $streamSocketAccept
  ){
    $streamSocketGetName = stream_socket_get_name(
      $this->streamSocketAccept,
      true
    );

    [ $this->ip, $this->port ] = preg_split(
      "#\:#", $streamSocketGetName
    );
  }

  public function getIp(
  ): string {
    return $this->ip;
  }

  public function getPort(
  ): string {
    return $this->port;
  }
}