<?php

namespace Websyspro\Enums;

enum ControllerType {
  case Controller;
  case Middleware;
  case Endpoint;
  case Parameter;
}