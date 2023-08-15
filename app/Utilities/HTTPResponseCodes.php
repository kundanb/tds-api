<?php

namespace App\Utilities;

class HTTPResponseCodes
{
    public const OK = 200;
    public const BAD_REQUEST = 400;
    public const UNAUTHORIZED = 401;
    public const PAYMENT_REQUIRED = 402;
    public const FORBIDDEN = 403;
    public const NOT_FOUND = 404;
    public const NOT_ACCEPTABLE = 406;
    public const UNSUPPORTED_MEDIA_TYPE = 415;
    public const INTERNAL_SERVER_ERROR = 500;
    public const SERVICE_UNAVAILABLE = 503;
}
