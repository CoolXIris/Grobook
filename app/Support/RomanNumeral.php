<?php

namespace App\Support;

class RomanNumeral
{
    /**
     * Get map of even integers (2..100) to their Roman numeral representation.
     *
     * @return array<int, string>
     */
    public static function evenUpTo100(): array
    {
        $result = [];

        for ($i = 2; $i <= 100; $i += 2) {
            $result[$i] = self::fromInt($i);
        }

        return $result;
    }

    public static function fromInt(?int $number): string
    {
        if ($number === null || $number < 1 || $number > 3999) {
            return '';
        }

        $map = [
            'M' => 1000,
            'CM' => 900,
            'D' => 500,
            'CD' => 400,
            'C' => 100,
            'XC' => 90,
            'L' => 50,
            'XL' => 40,
            'X' => 10,
            'IX' => 9,
            'V' => 5,
            'IV' => 4,
            'I' => 1,
        ];

        $roman = '';
        foreach ($map as $symbol => $value) {
            while ($number >= $value) {
                $roman .= $symbol;
                $number -= $value;
            }
        }

        return $roman;
    }

    public static function toEvenInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            $int = (int) $value;

            return ($int >= 2 && $int <= 100 && $int % 2 === 0) ? $int : null;
        }

        if (! is_string($value)) {
            return null;
        }

        $normalized = mb_strtoupper(trim($value));
        $flipped = array_flip(self::evenUpTo100());

        return $flipped[$normalized] ?? null;
    }
}
