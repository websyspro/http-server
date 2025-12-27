<?php

namespace Websyspro\Exceptions;

use Exception;
use Websyspro\Response;

class Error
{
  public static function badRequest(
    string $message
  ): Exception {
    return throw new Exception(
      message: $message, code: Response::HTTP_BAD_REQUEST
    );
  }

  public static function notFound(
    string $message
  ): Exception {
    return throw new Exception(
      message: $message, code: Response::HTTP_NOT_FOUND
    );
  }

  public static function serviceUnavailableError(
    string $message
  ): Exception {
    return throw new Exception(
      message: $message, code: Response::HTTP_SERVICE_UNAVAILABLE
    );
  }  

  public static function internalServerError(
    string $message
  ): Exception {
    return throw new Exception(
      message: $message, code: Response::HTTP_INTERNAL_SERVER_ERROR
    );
  }

  public static function unauthorized(
    string $message
  ): Exception {
    return throw new Exception(
      message: $message, code: Response::HTTP_UNAUTHORIZED
    );
  }
}