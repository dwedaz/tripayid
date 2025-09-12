<?php

namespace Tripay\PPOB\DTO\Response;

use Tripay\PPOB\DTO\DataTransferObject;

class ServerResponse extends DataTransferObject
{
    public readonly bool $success;
    public readonly string $message;
}