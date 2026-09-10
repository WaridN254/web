<?php

namespace App\Support;

class Code128
{
    protected static array $patterns = [
        0 => '212222', 1 => '222122', 2 => '222221', 3 => '121223', 4 => '121322',
        5 => '131222', 6 => '122213', 7 => '122312', 8 => '132212', 9 => '221213',
        10 => '221312', 11 => '231212', 12 => '112232', 13 => '122132', 14 => '122231',
        15 => '113222', 16 => '123122', 17 => '123221', 18 => '223211', 19 => '221132',
        20 => '221231', 21 => '213212', 22 => '223112', 23 => '312131', 24 => '311222',
        25 => '321122', 26 => '321221', 27 => '312212', 28 => '322112', 29 => '322211',
        30 => '212123', 31 => '212321', 32 => '232121', 33 => '111323', 34 => '131123',
        35 => '131321', 36 => '112313', 37 => '132113', 38 => '132311', 39 => '211313',
        40 => '231113', 41 => '231311', 42 => '112133', 43 => '112331', 44 => '132131',
        45 => '113123', 46 => '113321', 47 => '133121', 48 => '313121', 49 => '211331',
        50 => '231131', 51 => '213113', 52 => '213311', 53 => '213131', 54 => '311123',
        55 => '311321', 56 => '331121', 57 => '312113', 58 => '312311', 59 => '332111',
        60 => '314111', 61 => '221411', 62 => '431111', 63 => '111224', 64 => '111422',
        65 => '121124', 66 => '121421', 67 => '141122', 68 => '141221', 69 => '112214',
        70 => '112412', 71 => '122114', 72 => '122411', 73 => '142112', 74 => '142211',
        75 => '241211', 76 => '221114', 77 => '413111', 78 => '241112', 79 => '134111',
        80 => '111242', 81 => '121142', 82 => '121241', 83 => '114212', 84 => '124112',
        85 => '124211', 86 => '411212', 87 => '421112', 88 => '421211', 89 => '212141',
        90 => '214121', 91 => '412121', 92 => '111143', 93 => '111341', 94 => '131141',
        95 => '114113', 96 => '114311', 97 => '411113', 98 => '411311', 99 => '113141',
        100 => '114131', 101 => '311141', 102 => '411131',
        103 => '211412', 104 => '211214', 105 => '211232',
    ];

    protected static string $stop = '2331112';

    public static function svg(string $text, int $height = 50, string $barColor = '#0f172a'): string
    {
        $text = (string) $text;
        if ($text === '') {
            return '';
        }

        $symbols = [104];

        foreach (str_split($text) as $char) {
            $value = ord($char) - 32;
            if ($value < 0 || $value > 95) {
                $value = 95;
            }
            $symbols[] = $value;
        }

        $checksum = 104;
        foreach (array_slice($symbols, 1) as $position => $value) {
            $checksum += $value * ($position + 1);
        }
        $symbols[] = $checksum % 103;

        $patterns = [];
        foreach ($symbols as $symbol) {
            $patterns[] = self::$patterns[$symbol];
        }
        $patterns[] = self::$stop;

        $bars = [];
        $position = 0;
        foreach ($patterns as $pattern) {
            $isBar = true;
            foreach (str_split($pattern) as $width) {
                $width = (int) $width;
                if ($isBar) {
                    $bars[] = [$position, $width];
                }
                $position += $width;
                $isBar = ! $isBar;
            }
        }

        $scale = 1.6;
        $width = round($position * $scale, 1);

        $rects = [];
        foreach ($bars as [$x, $barWidth]) {
            $rects[] = sprintf(
                '<rect x="%.1f" y="0" width="%.1f" height="%d" fill="%s"/>',
                $x * $scale,
                $barWidth * $scale,
                $height,
                $barColor
            );
        }

        return '<svg xmlns="http://www.w3.org/2000/svg" width="' . $width . '" height="' . $height
            . '" viewBox="0 0 ' . $width . ' ' . $height . '" shape-rendering="crispEdges">'
            . implode('', $rects) . '</svg>';
    }
}
