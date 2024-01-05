<?php

declare(strict_types=1);

namespace LeKoala;

class F
{
    private const W = [
        'cupiditate', 'praesentium', 'voluptas', 'pariatur',
        'cum', 'lorem', 'ipsum', 'loquor', 'sic', 'amet'
    ];
    private const F = ['Julia', 'Lucius', 'Julius', 'Anna'];
    private const S = ['Maximus', 'Corneli', 'Postumius', 'Servilius'];
    private const C = ['US', 'NZ', 'FR', 'BE', 'NL', 'IT', 'UK'];
    private const P = ['Roma', 'Caesera', 'Florentia', 'Lutetia'];
    private const L = ['fr_FR', 'fr_BE', 'nl_BE', 'nl_NL', 'en_US', 'en_NZ', 'en_UK', 'it_IT'];

    public static function pick(string $a, string $b): string
    {
        return random_int(0, 1) === 1 ? $a : $b;
    }

    public static function picka(array $arr, int $c = 1): array
    {
        $r = [];
        while ($c > 0) {
            $c--;
            $r[] = $arr[array_rand($arr)];
        }
        return $r;
    }

    public static function d(): string
    {
        return date('Y-m-d', strtotime(self::pick('+', '-') . random_int(1, 365) . ' days'));
    }

    public static function t(): string
    {
        return sprintf('%02d:%02d:%02d', random_int(0, 23), random_int(0, 59), random_int(0, 59));
    }

    public static function dt(): string
    {
        return self::d() . ' ' . self::t();
    }

    public static function dtz(): string
    {
        return self::d() . 'T' . self::t() . 'Z';
    }

    public static function i(int $a = -100, int $b = 100): int
    {
        return random_int($a, $b);
    }

    public static function ctry(): string
    {
        return self::C[array_rand(self::C)];
    }

    public static function fn(): string
    {
        return self::F[array_rand(self::F)];
    }

    public static function sn(): string
    {
        return self::S[array_rand(self::S)];
    }

    public static function dom(): string
    {
        return self::W[array_rand(self::W)] . '.dev';
    }

    public static function w($a = 5, $b = 10): string
    {
        return implode(' ', self::picka(self::W, random_int($a, $b)));
    }

    public static function uw($a = 5, $b = 10): string
    {
        return ucfirst(self::w($a, $b));
    }

    public static function b(): bool
    {
        return (bool)random_int(0, 1);
    }

    public static function p(): string
    {
        return self::P[array_rand(self::P)];
    }

    public static function addr(): string
    {
        return 'via ' . self::w(1, 1) . ', ' . self::i(1, 20) . ' - ' . self::i(1000, 9999) . ' ' . self::p();
    }

    public static function l(string $ctry = null): string
    {
        do {
            $l = self::L[array_rand(self::L)];
        } while ($ctry !== null && !str_contains($l, $ctry));
        return $l;
    }

    public static function lg(): string
    {
        return explode('_', self::l())[0];
    }

    public static function m(int $a = 10_000, int $b = 100_000): string
    {
        return number_format(self::i($a, $b)) . ' ' . self::pick('€', '$');
    }

    public static function em(string $p = null): string
    {
        if ($p === null) {
            $p = self::fn();
        }
        return strtolower($p) . '@' . self::dom();
    }
}
