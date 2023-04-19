<?php
class UnauthorizedException extends BaseException
{
    #[\JetBrains\PhpStorm\Pure] public function __construct(string $message = "Neoprávněn provádět změny", int $code = 401, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}