<?php
class ForbiddenDelete extends BaseException
{
    #[\JetBrains\PhpStorm\Pure] public function __construct(string $message = "Nelze smazat domovskou místnost", int $code = 403, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}