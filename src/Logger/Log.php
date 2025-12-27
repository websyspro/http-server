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
    string $logIp = "::1",
    string $logPort = "00000"   
  ): string {
    return "[{$logIp}]:{$logPort}";
  }

  private static function isStartTimer(
  ): void {
    if(isset(Log::$startTimer) === false){
      Log::setStartTimer();
    }
  }
  
  public static function message(
    LogType $logType,
    string $logText,
    string $logIp = "::1",
    string $logPort = "00000" 
  ): bool {
    Log::isStartTimer();
    fwrite( fopen('php://stdout', 'w'), (
      sprintf("\x1b[37m%s %s\x1b[32m LOG \x1b[33m[{$logType->value}] \x1b[32m{$logText}\x1b[37m \x1b[37m+%sms\n", 
        ... [
          Log::getNow(),
          Log::getOrigem(
            $logIp,
            $logPort
          ),
          Log::getNowTimer()
        ]
      )
    ));

    return true;
  }

  public static function error(
    LogType $logType,
    string $logText,
    string $logIp = "::1",
    string $logPort = "00000"       
  ): bool {
    Log::isStartTimer();
    fwrite( fopen('php://stdout', 'w'), (
      sprintf( "\x1b[37m%s %s\x1b[32m LOG \x1b[33m[{$logType->value}] \x1b[31m{$logText} \x1b[37m+%sms\n",
        ... [
          Log::getNow(),
          Log::getOrigem(
            $logIp,
            $logPort
          ),
          Log::getNowTimer()
        ]
      )
    ));

    return false;
  }
}