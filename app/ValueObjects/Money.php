<?php

namespace App\ValueObjects;

readonly class Money
{
    private function __construct(
        private int $cents,
    ) {}

    public static function fromDecimal(float|string $decimal): self
    {
        return new self((int) round((float) $decimal * 100));
    }

    public static function fromCents(int $cents): self
    {
        return new self($cents);
    }

    public function toDecimal(): float
    {
        return $this->cents / 100;
    }

    public function multiply(int $factor): self
    {
        return new self($this->cents * $factor);
    }

    public function add(self $other): self
    {
        return new self($this->cents + $other->cents);
    }

    public function format(string $symbol = '$'): string
    {
        return $symbol . number_format($this->toDecimal(), 2);
    }

    public function getCents(): int
    {
        return $this->cents;
    }
}
