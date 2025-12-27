<?php

namespace Websyspro\Enums;

enum RequestMethod
{
	case GET;
	case POST;
	case PUT;
	case PATCH;
	case DELETE;
	case HEAD;
	case OPTIONS;
}