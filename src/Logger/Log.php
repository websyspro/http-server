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

  public static function getNowTimer(
  ): int { 
    $starDiff = round(( 
      microtime(true) - Log::$startTimer
    ) * 1000);

    Log::setStartTimer(); 
    return $starDiff;
  }

  private static function getNow(
  ): string {
    return date( "[d/m/Y H:i:s]" );
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
      sprintf("\x1b[37m%s\x1b[32m Log \x1b[33m[{$logType->value}] \x1b[32m{$logText}\x1b[37m \x1b[37m+%sms\n", 
        ... [
          Log::getNow(),
          Log::getNowTimer()
        ]
      )
    ));

    Log::getNowTimer();

    return true;
  }

  public static function error(
    LogType $logType,
    string $logText    
  ): bool {
    Log::isStartTimer();
    fwrite( fopen('php://stdout', 'w'), (
      sprintf( "\x1b[37m%s\x1b[32m Log \x1b[33m[{$logType->value}] \x1b[31m{$logText} \x1b[37m+%sms\n",
        ... [
          Log::getNow(),
          Log::getNowTimer()
        ]
      )
    ));

    Log::getNowTimer();

    return false;
  }
}