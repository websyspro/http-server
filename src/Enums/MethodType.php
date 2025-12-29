<?php

namespace Websyspro\Enums;

enum MethodType
{
	case GET;
	case POST;
	case PUT;
	case PATCH;
	case DELETE;
	case HEAD;
	case OPTIONS;
}