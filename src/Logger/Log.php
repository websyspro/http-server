<?php

namespace Websyspro\Logger;

use Websyspro\Logger\Enums\LogType;

class Log
{
  public static float $startTimer;

  private static function setStartTimer(
  ): void {
    Log::$startTimer = microtime(true);
  }

  private static function getNowTimer(
  ): int { 
    $starDiff = round(( 
      microtime(true) - Log::$startTimer
    ) * 1000);

    Log::setStartTimer(); 
    return $starDiff;
  }

  private static function getNow(
  ): string {
    return date( "[D M d H:i:s Y]" );
  }

  private static function getOrigem(
  ): string {
    $remoteAddr = null;
    $serverPort = null;

    if(isset($_SERVER["REMOTE_ADDR"])){
      [ "REMOTE_ADDR" => $remoteAddr, 
        "SERVER_PORT" => $serverPort ] = $_SERVER;

      if($serverPort !== null){
        $serverPort = str_pad(
          $serverPort, 5, "0", STR_PAD_LEFT
        );
      }  
    }
  
    return $remoteAddr !== null && $serverPort !== null
      ? "[{$remoteAddr}]:{$serverPort}"
      : "[::1]:00000";
  }

  private static function isStartTimer(
  ): void {
    if(isset(Log::$startTimer) === false){
      Log::setStartTimer();
    }
  }
  
  public static function message(
    LogType $logType,
    string $logText
  ): bool {
    Log::isStartTimer();
    fwrite( fopen('php://stdout', 'w'), (
      sprintf("\x1b[37m%s %s\x1b[32m LOG \x1b[33m[{$logType->value}] \x1b[32m{$logText}\x1b[37m \x1b[37m+%sms\n", 
        Log::getNow(),
        Log::getOrigem(),
        Log::getNowTimer(),
      )
    ));

    return true;
  }

  public static function error(
    LogType $logType,
    string $logText      
  ): bool {
    Log::isStartTimer();
    fwrite( fopen('php://stdout', 'w'), (
      sprintf( "\x1b[37m%s %s\x1b[32m LOG \x1b[33m[{$logType->value}] \x1b[31m{$logText} \x1b[37m+%sms\n",
        Log::getNow(),
        Log::getOrigem(),
        Log::getNowTimer(),
      )
    ));

    return false;
  }
}