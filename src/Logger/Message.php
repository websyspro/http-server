<?php

namespace Websyspro\Logger;

use Websyspro\Logger\Enums\LogType;

class Message
{
  public static function infors(
    LogType $logType,
    string $logText    
  ): bool {
    return Log::message($logType, $logText);
  }

  public static function error(
    LogType $logType,
    string $logText    
  ): bool {
    return Log::error($logType, $logText);
  }
}