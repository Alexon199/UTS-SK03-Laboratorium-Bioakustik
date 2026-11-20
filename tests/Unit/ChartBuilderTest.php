<?php

namespace Tests\Unit;

use App\Support\ChartBuilder;
use App\Support\Fmt;
use PHPUnit\Framework\TestCase;

/** ChartBuilder tidak memakai Laravel, jadi ini tes unit murni (tanpa database). */
class ChartBuilderTest extends TestCase
{
    public function test_donut_segments_cover_the_whole_circle(): void
    {
        $chart = ChartBuilder::donut([
            ['label' => 'A', 'value' => 6],
            ['label' => 'B', 'value' => 3],
            ['label' => 'C', 'value' => 1],
            ['label' => 'Kosong', 'value' => 0], // nilai 0 dibuang
        ]);

        $this->assertSame(10, $chart['total']);
        $this->assertCount(3, $chart['segments']);
        $this->assertSame(60.0, $chart['segments'][0]['pct']);
        $this->assertSame(10.0, $chart['segments'][2]['pct']);
    }

    public function test_donut_with_no_data_is_empty(): void
    {
        $chart = ChartBuilder::donut([]);

        $this->assertSame(0, $chart['total']);
        $this->assertSame([], $chart['segments']);
    }

    public function test_bars_are_scaled_against_the_largest_value(): void
    {
        $bars = ChartBuilder::bars([
            ['label' => 'x', 'value' => 8],
            ['label' => 'y', 'value' => 2],
        ]);

        $this->assertSame(100.0, $bars[0]['pct']);
        $this->assertSame(25.0, $bars[1]['pct']);
    }

    public function test_bars_with_all_zero_do_not_divide_by_zero(): void
    {
        $bars = ChartBuilder::bars([['label' => 'x', 'value' => 0]]);

        $this->assertSame(0.0, $bars[0]['pct']);
    }

    public function test_area_chart_has_one_dot_per_point_and_a_closed_area(): void
    {
        $chart = ChartBuilder::area([
            ['label' => 'Jun', 'value' => 1],
            ['label' => 'Jul', 'value' => 5],
            ['label' => 'Agu', 'value' => 2],
        ]);

        $this->assertCount(3, $chart['dots']);
        $this->assertSame(8, $chart['total']);
        $this->assertStringStartsWith('M', $chart['line']);
        $this->assertStringEndsWith('Z', $chart['area']);
        // titik tertinggi (5) harus berada di atas titik terendah (1): y lebih kecil
        $this->assertLessThan($chart['dots'][0]['y'], $chart['dots'][1]['y']);
    }

    public function test_area_chart_handles_empty_input(): void
    {
        $chart = ChartBuilder::area([]);

        $this->assertSame([], $chart['dots']);
        $this->assertSame('', $chart['area']);
    }

    public function test_map_places_points_inside_the_box_even_when_out_of_range(): void
    {
        $map = ChartBuilder::map([
            ['lat' => -6.7, 'lon' => 107.0, 'label' => 'Jawa'],
            ['lat' => 80.0, 'lon' => 10.0, 'label' => 'Di luar Nusantara'],
        ]);

        foreach ($map['dots'] as $dot) {
            $this->assertGreaterThanOrEqual(0, $dot['x']);
            $this->assertLessThanOrEqual($map['width'], $dot['x']);
            $this->assertGreaterThanOrEqual(0, $dot['y']);
            $this->assertLessThanOrEqual($map['height'], $dot['y']);
        }
    }

    public function test_spectrogram_skips_annotations_without_frequency(): void
    {
        $row = fn (int $i, ?int $low, ?int $high) => [
            'idx' => $i, 'label' => 'x', 'type' => 'song', 'type_label' => 'Nyanyian', 'color' => 1,
            'start' => 1.0, 'end' => 9.0, 'low' => $low, 'high' => $high,
        ];

        $chart = ChartBuilder::spectrogram([$row(0, 400, 1800), $row(1, null, null)]);

        $this->assertSame(1, $chart['plotted']);
        $this->assertSame(1, $chart['skipped']);
        $this->assertCount(1, $chart['boxes']);
        $this->assertGreaterThan(0, $chart['boxes'][0]['w']);
        $this->assertGreaterThan(0, $chart['boxes'][0]['h']);
    }

    public function test_fmt_uses_indonesian_number_format(): void
    {
        $this->assertSame('1.234,5', Fmt::num(1234.5, 1));
        $this->assertSame('Mei', Fmt::monthShort(5));
        $this->assertSame(
            ['common' => 'Owa Jawa', 'latin' => 'Hylobates moloch'],
            Fmt::species('Owa Jawa (Hylobates moloch)')
        );
        $this->assertSame(['common' => 'Kutilang', 'latin' => null], Fmt::species('Kutilang'));
    }
}
