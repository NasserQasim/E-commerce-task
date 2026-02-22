<?php

namespace App\DTOs;

readonly class ServiceResult
{
    public function __construct(
        public bool $success,
        public string $message,
    ) {}

    public static function success(string $message): static
    {
        return new static(true, $message);
    }

    public static function failure(string $message): static
    {
        return new static(false, $message);
    }
}
