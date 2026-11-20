<?php

namespace App\Support;

/** Pemformat angka/teks gaya Indonesia (koma desimal, titik ribuan). Tanpa dependensi Laravel. */
final class Fmt
{
    private const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    public static function num(int|float|string|null $value, int $decimals = 0): string
    {
        return number_format((float) $value, $decimals, ',', '.');
    }

    public static function monthShort(int $month): string
    {
        return self::MONTHS[($month - 1) % 12];
    }

    /**
     * Pisahkan "Owa Jawa (Hylobates moloch)" menjadi nama umum dan nama ilmiah.
     *
     * @return array{common: string, latin: ?string}
     */
    public static function species(string $name): array
    {
        if (preg_match('/^(.*?)\s*\((.+)\)\s*$/u', $name, $m)) {
            return ['common' => $m[1], 'latin' => $m[2]];
        }

        return ['common' => $name, 'latin' => null];
    }
}
