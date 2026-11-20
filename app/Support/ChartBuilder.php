<?php

namespace App\Support;

/**
 * Menghitung geometri grafik (donat, batang, area, peta titik, spektrogram) di PHP,
 * sehingga view Blade hanya perlu mengulang data matang menjadi elemen SVG.
 *
 * Semua metode bersifat murni (input array -> output array) dan tidak bergantung pada Laravel,
 * jadi mudah dites dan mudah dijelaskan: "controller menyiapkan angka, view menggambar".
 */
final class ChartBuilder
{
    /**
     * Donat: setiap irisan = satu lingkaran dengan stroke-dasharray.
     *
     * @param  array<int, array{label: string, value: int|float, idx?: int}>  $items
     */
    public static function donut(array $items, float $radius = 70.0, float $stroke = 28.0): array
    {
        $items = array_values(array_filter($items, fn ($i) => ($i['value'] ?? 0) > 0));
        $total = (float) array_sum(array_column($items, 'value'));
        $circumference = 2 * M_PI * $radius;
        $cursor = 0.0;
        $segments = [];

        foreach ($items as $position => $item) {
            $share = $total > 0 ? $item['value'] / $total : 0.0;
            $length = $share * $circumference;
            $gap = count($items) > 1 ? min(3.0, $length * 0.25) : 0.0;
            $visible = max(0.0, $length - $gap);

            $segments[] = $item + ['idx' => $position] + [
                'pct' => round($share * 100, 1),
                'length' => round($visible, 2),
                'rest' => round($circumference - $visible, 2),
                'offset' => round(-$cursor, 2),
            ];

            $cursor += $length;
        }

        return [
            'total' => (int) $total,
            'radius' => $radius,
            'stroke' => $stroke,
            'circumference' => round($circumference, 2),
            'segments' => $segments,
        ];
    }

    /**
     * Batang horizontal: tiap nilai diskalakan terhadap nilai terbesar (0-100%).
     *
     * @param  array<int, array{label: string, value: int|float}>  $items
     * @return array<int, array<string, mixed>>
     */
    public static function bars(array $items, ?float $max = null): array
    {
        $values = array_column($items, 'value');
        $max ??= $values ? (float) max($values) : 0.0;

        return array_map(
            fn (array $item, int $i) => $item + ['idx' => $i] + [
                'pct' => $max > 0 ? round($item['value'] / $max * 100, 1) : 0.0,
            ],
            $items,
            array_keys($items)
        );
    }

    /**
     * Grafik area/garis dengan kurva halus.
     *
     * @param  array<int, array{label: string, value: int|float}>  $points
     */
    public static function area(array $points, float $width = 640, float $height = 300): array
    {
        [$left, $right, $top, $bottom] = [40.0, 18.0, 16.0, 30.0];
        $plotW = $width - $left - $right;
        $plotH = $height - $top - $bottom;
        $baseY = $top + $plotH;

        $values = array_column($points, 'value');
        $max = max(4, $values ? (int) ceil(max($values)) : 0);
        $step = self::niceStep($max / 4);
        $ceiling = ceil($max / $step) * $step;

        $count = count($points);
        $dots = [];
        foreach (array_values($points) as $i => $p) {
            $x = $count > 1 ? $left + $plotW * $i / ($count - 1) : $left + $plotW / 2;
            $y = $top + $plotH - ($p['value'] / $ceiling) * $plotH;
            $dots[] = ['x' => round($x, 1), 'y' => round($y, 1), 'label' => $p['label'], 'value' => $p['value']];
        }

        $line = '';
        foreach ($dots as $i => $d) {
            if ($i === 0) {
                $line = "M{$d['x']} {$d['y']}";

                continue;
            }
            $prev = $dots[$i - 1];
            $mid = round(($prev['x'] + $d['x']) / 2, 1);
            $line .= " C{$mid} {$prev['y']} {$mid} {$d['y']} {$d['x']} {$d['y']}";
        }

        $area = $dots
            ? $line." L{$dots[$count - 1]['x']} {$baseY} L{$dots[0]['x']} {$baseY} Z"
            : '';

        $yTicks = [];
        for ($v = 0.0; $v <= $ceiling + 0.0001; $v += $step) {
            $yTicks[] = ['y' => round($top + $plotH - ($v / $ceiling) * $plotH, 1), 'label' => Fmt::num($v)];
        }

        return [
            'width' => $width, 'height' => $height,
            'left' => $left, 'right' => $width - $right, 'baseY' => $baseY,
            'line' => $line, 'area' => $area,
            'dots' => $dots, 'yTicks' => $yTicks,
            'total' => (int) array_sum($values),
        ];
    }

    /**
     * Peta titik: koordinat (lintang, bujur) dipetakan ke kotak yang menutupi Nusantara.
     * Ini plot koordinat dengan garis lintang/bujur, bukan peta dengan garis pantai.
     *
     * @param  array<int, array{lat: float, lon: float, label: string}>  $points
     */
    public static function map(array $points, float $width = 640, float $height = 300): array
    {
        [$lonMin, $lonMax, $latMin, $latMax] = [94.0, 142.0, -12.0, 7.0];

        $project = fn (float $lat, float $lon): array => [
            round(max(0, min($width, ($lon - $lonMin) / ($lonMax - $lonMin) * $width)), 1),
            round(max(0, min($height, ($latMax - $lat) / ($latMax - $latMin) * $height)), 1),
        ];

        $vertical = [];
        foreach ([100, 110, 120, 130, 140] as $lon) {
            $vertical[] = ['x' => $project(0, $lon)[0], 'label' => $lon.'°BT'];
        }

        $horizontal = [];
        foreach ([5 => '5°LU', 0 => '0°', -5 => '5°LS', -10 => '10°LS'] as $lat => $label) {
            $horizontal[] = ['y' => $project($lat, 100)[1], 'label' => $label];
        }

        $dots = [];
        foreach (array_values($points) as $i => $p) {
            [$x, $y] = $project((float) $p['lat'], (float) $p['lon']);
            $dots[] = $p + ['x' => $x, 'y' => $y, 'i' => $i, 'idx' => 0];
        }

        return ['width' => $width, 'height' => $height, 'vertical' => $vertical, 'horizontal' => $horizontal, 'dots' => $dots];
    }

    /**
     * Sketsa spektrogram: sumbu X waktu (detik), sumbu Y frekuensi (kHz).
     * Anotasi tanpa batas frekuensi tidak digambar dan dihitung di 'skipped'.
     *
     * @param  array<int, array{idx: int, label: string, type: string, type_label: string, color: int, start: float, end: float, low: ?int, high: ?int}>  $rows
     */
    public static function spectrogram(array $rows, float $width = 760, float $height = 290): array
    {
        [$left, $right, $top, $bottom] = [54.0, 14.0, 14.0, 36.0];
        $plotW = $width - $left - $right;
        $plotH = $height - $top - $bottom;

        $plotted = array_values(array_filter($rows, fn ($r) => $r['low'] !== null && $r['high'] !== null));
        $result = [
            'width' => $width, 'height' => $height,
            'left' => $left, 'top' => $top, 'plotW' => $plotW, 'plotH' => $plotH,
            'boxes' => [], 'xTicks' => [], 'yTicks' => [],
            'plotted' => count($plotted), 'skipped' => count($rows) - count($plotted),
        ];

        if (! $plotted) {
            return $result;
        }

        $maxTime = max(5.0, ceil(max(array_column($rows, 'end')) / 5) * 5);
        $freqStep = self::niceStep(max(1000.0, (float) max(array_column($plotted, 'high'))) / 4);
        $maxFreq = ceil(max(array_column($plotted, 'high')) / $freqStep) * $freqStep;
        $maxFreq = max($maxFreq, $freqStep * 4);

        $x = fn (float $t): float => $left + $t / $maxTime * $plotW;
        $y = fn (float $f): float => $top + $plotH - $f / $maxFreq * $plotH;

        for ($k = 0; $k <= 5; $k++) {
            $t = $maxTime / 5 * $k;
            $result['xTicks'][] = ['x' => round($x($t), 1), 'label' => Fmt::num($t)];
        }

        for ($f = 0.0; $f <= $maxFreq + 0.0001; $f += $freqStep) {
            $result['yTicks'][] = ['y' => round($y($f), 1), 'label' => Fmt::num($f / 1000, $freqStep < 1000 ? 1 : 0)];
        }

        foreach ($plotted as $r) {
            $bx = $x((float) $r['start']);
            $by = $y((float) $r['high']);
            $result['boxes'][] = $r + [
                'x' => round($bx, 1),
                'y' => round($by, 1),
                'w' => round(max(4, $x((float) $r['end']) - $bx), 1),
                'h' => round(max(4, $y((float) $r['low']) - $by), 1),
            ];
        }

        return $result;
    }

    /** Langkah sumbu "bulat" (1, 2, 5, 10, 20, 50, ...). */
    private static function niceStep(float $raw): float
    {
        if ($raw <= 1) {
            return 1.0;
        }

        $base = 10 ** floor(log10($raw));
        $factor = $raw / $base;

        return $base * match (true) {
            $factor <= 1 => 1,
            $factor <= 2 => 2,
            $factor <= 5 => 5,
            default => 10,
        };
    }
}
