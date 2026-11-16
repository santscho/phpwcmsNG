<?php
/**
 * phpwcms content management system
 *
 * @author Oliver Georgi <og@phpwcms.org>
 * @copyright Copyright (c) 2002-2026, Oliver Georgi
 * @license http://opensource.org/licenses/GPL-2.0 GNU GPL-2
 * @link http://www.phpwcms.org
 *
 **/

// ----------------------------------------------------------------
// obligate check for phpwcms constants
if (!defined('PHPWCMS_ROOT')) {
    die("You Cannot Access This Script Directly, Have a Nice Day.");
}
// ----------------------------------------------------------------

/**
 * Minimal QR code encoder (byte mode) that renders an inline SVG.
 *
 * Self contained on purpose, like the TOTP helper: the proof of concept
 * should not pull in a composer package only to show a single code.
 * Covers versions 1 to 10, which is far more than an otpauth:// URI needs,
 * with all four error correction levels.
 */
class PhpwcmsQrCode {

    public const ECC_L = 0;
    public const ECC_M = 1;
    public const ECC_Q = 2;
    public const ECC_H = 3;

    // format information bits per level, index matches the constants above
    private const FORMAT_BITS = [1, 0, 3, 2];

    // error correction codewords per block, [level][version - 1]
    private const ECC_PER_BLOCK = [
        [7, 10, 15, 20, 26, 18, 20, 24, 30, 18],
        [10, 16, 26, 18, 24, 16, 18, 22, 22, 26],
        [13, 22, 18, 26, 18, 24, 18, 22, 20, 24],
        [17, 28, 22, 16, 22, 28, 26, 26, 24, 28],
    ];

    // number of error correction blocks, [level][version - 1]
    private const BLOCKS = [
        [1, 1, 1, 1, 1, 2, 2, 2, 2, 4],
        [1, 1, 1, 2, 2, 4, 4, 4, 5, 5],
        [1, 1, 2, 2, 4, 4, 6, 6, 8, 8],
        [1, 1, 2, 4, 4, 4, 5, 6, 8, 8],
    ];

    // total codewords per version
    private const TOTAL_CODEWORDS = [26, 44, 70, 100, 134, 172, 196, 242, 292, 346];

    // remainder bits after the codewords, per version
    private const REMAINDER_BITS = [0, 7, 7, 7, 7, 7, 0, 0, 0, 0];

    // alignment pattern centres, per version
    private const ALIGNMENT = [
        2 => [6, 18], 3 => [6, 22], 4 => [6, 26], 5 => [6, 30], 6 => [6, 34],
        7 => [6, 22, 38], 8 => [6, 24, 42], 9 => [6, 26, 46], 10 => [6, 28, 50],
    ];

    // Galois field tables, built once per request
    private static array $expTable = [];
    private static array $logTable = [];

    /** @var array<int, array<int, bool>> matrix rows, true is a dark module */
    private array $modules = [];
    /** @var array<int, array<int, bool>> modules that belong to a pattern */
    private array $reserved = [];
    private int $size = 0;
    private int $version = 0;
    private int $level = self::ECC_M;

    /**
     * The module matrix for a text, true meaning a dark module.
     *
     * @return array<int, array<int, bool>>
     */
    public static function matrix(string $text, int $level = self::ECC_M): array
    {
        $code = new self();
        $code->level   = $level;
        $code->version = $code->fitsIn($text);
        $code->size    = $code->version * 4 + 17;
        $code->modules = array_fill(0, $code->size, array_fill(0, $code->size, false));
        $code->reserved = $code->modules;

        $code->drawFunctionPatterns();
        $code->drawCodewords($code->buildCodewords($text));
        $code->drawBestMask();

        return $code->modules;
    }

    /**
     * The code as a standalone SVG element, ready to echo.
     */
    public static function svg(
        string $text,
        int $level = self::ECC_M,
        string $label = '',
        int $border = 4,
        int $pixels = 220
    ): string {
        $matrix = self::matrix($text, $level);
        $width  = count($matrix);
        $canvas = $width + $border * 2;
        $path   = '';

        foreach ($matrix as $y => $row) {
            $x = 0;
            while ($x < $width) {
                if (!$row[$x]) {
                    $x++;
                    continue;
                }
                $run = 1;
                while ($x + $run < $width && $row[$x + $run]) {
                    $run++;
                }
                $path .= 'M' . ($x + $border) . ' ' . ($y + $border) . 'h' . $run . 'v1h-' . $run . 'z';
                $x += $run;
            }
        }

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $canvas . ' ' . $canvas . '"'
            . ' width="' . $pixels . '" height="' . $pixels . '"'
            . ' shape-rendering="crispEdges" role="img"'
            . ($label !== '' ? ' aria-label="' . html($label) . '"' : ' aria-hidden="true"')
            . '><rect width="' . $canvas . '" height="' . $canvas . '" fill="#fff" />'
            . '<path fill="#000" d="' . $path . '" /></svg>';
    }

    /**
     * The smallest version the text fits into.
     */
    private function fitsIn(string $text): int
    {
        $length = strlen($text);
        for ($version = 1; $version <= 10; $version++) {
            $countBits = $version <= 9 ? 8 : 16;
            if (4 + $countBits + 8 * $length <= $this->dataCodewords($version) * 8) {
                return $version;
            }
        }
        throw new RuntimeException('The text does not fit into a QR code of version 10.');
    }

    private function dataCodewords(int $version): int
    {
        return self::TOTAL_CODEWORDS[$version - 1]
            - self::ECC_PER_BLOCK[$this->level][$version - 1] * self::BLOCKS[$this->level][$version - 1];
    }

    /**
     * Encoded, error corrected and interleaved codewords.
     *
     * @return array<int, int>
     */
    private function buildCodewords(string $text): array
    {
        $countBits = $this->version <= 9 ? 8 : 16;
        $total     = $this->dataCodewords($this->version);
        $capacity  = $total * 8;
        $bits      = [0, 1, 0, 0];

        $length = strlen($text);
        for ($bit = $countBits - 1; $bit >= 0; $bit--) {
            $bits[] = ($length >> $bit) & 1;
        }
        for ($i = 0; $i < $length; $i++) {
            $byte = ord($text[$i]);
            for ($bit = 7; $bit >= 0; $bit--) {
                $bits[] = ($byte >> $bit) & 1;
            }
        }

        for ($i = 0; $i < 4 && count($bits) < $capacity; $i++) {
            $bits[] = 0;
        }
        while (count($bits) % 8 !== 0) {
            $bits[] = 0;
        }

        $data = [];
        foreach (str_split(implode('', $bits), 8) as $byte) {
            $data[] = bindec($byte);
        }
        for ($pad = 0; count($data) < $total; $pad++) {
            $data[] = $pad % 2 === 0 ? 0xEC : 0x11;
        }

        $eccLength = self::ECC_PER_BLOCK[$this->level][$this->version - 1];
        $blocks    = self::BLOCKS[$this->level][$this->version - 1];
        $shortSize = intdiv($total, $blocks);
        $shortRuns = $blocks - $total % $blocks;

        $dataBlocks = [];
        $eccBlocks  = [];
        $offset     = 0;
        for ($block = 0; $block < $blocks; $block++) {
            $size  = $shortSize + ($block < $shortRuns ? 0 : 1);
            $chunk = array_slice($data, $offset, $size);
            $offset += $size;
            $dataBlocks[] = $chunk;
            $eccBlocks[]  = $this->reedSolomon($chunk, $eccLength);
        }

        $codewords = [];
        for ($i = 0; $i <= $shortSize; $i++) {
            foreach ($dataBlocks as $block) {
                if ($i < count($block)) {
                    $codewords[] = $block[$i];
                }
            }
        }
        for ($i = 0; $i < $eccLength; $i++) {
            foreach ($eccBlocks as $block) {
                $codewords[] = $block[$i];
            }
        }

        return $codewords;
    }

    /**
     * Reed-Solomon error correction codewords for one block.
     *
     * @param array<int, int> $data
     * @return array<int, int>
     */
    private function reedSolomon(array $data, int $degree): array
    {
        $divisor = $this->divisor($degree);
        $result  = array_fill(0, $degree, 0);

        foreach ($data as $byte) {
            $factor = $byte ^ array_shift($result);
            $result[] = 0;
            foreach ($divisor as $index => $coefficient) {
                $result[$index] ^= $this->multiply($coefficient, $factor);
            }
        }

        return $result;
    }

    /**
     * The generator polynomial for a number of error correction codewords.
     *
     * @return array<int, int>
     */
    private function divisor(int $degree): array
    {
        $result = array_fill(0, $degree - 1, 0);
        $result[] = 1;
        $root = 1;

        for ($i = 0; $i < $degree; $i++) {
            for ($j = 0; $j < $degree; $j++) {
                $result[$j] = $this->multiply($result[$j], $root);
                if ($j + 1 < $degree) {
                    $result[$j] ^= $result[$j + 1];
                }
            }
            $root = $this->multiply($root, 0x02);
        }

        return $result;
    }

    private function multiply(int $x, int $y): int
    {
        if ($x === 0 || $y === 0) {
            return 0;
        }
        if (self::$expTable === []) {
            $value = 1;
            for ($i = 0; $i < 255; $i++) {
                self::$expTable[$i] = $value;
                self::$logTable[$value] = $i;
                $value <<= 1;
                if ($value & 0x100) {
                    $value ^= 0x11D;
                }
            }
            for ($i = 255; $i < 512; $i++) {
                self::$expTable[$i] = self::$expTable[$i - 255];
            }
        }

        return self::$expTable[self::$logTable[$x] + self::$logTable[$y]];
    }

    private function drawFunctionPatterns(): void
    {
        for ($i = 0; $i < $this->size; $i++) {
            $this->set(6, $i, $i % 2 === 0);
            $this->set($i, 6, $i % 2 === 0);
        }

        $this->drawFinder(3, 3);
        $this->drawFinder($this->size - 4, 3);
        $this->drawFinder(3, $this->size - 4);

        $alignment = self::ALIGNMENT[$this->version] ?? [6];
        $last      = count($alignment) - 1;
        foreach ($alignment as $i => $y) {
            foreach ($alignment as $j => $x) {
                if (($i === 0 && $j === 0) || ($i === 0 && $j === $last) || ($i === $last && $j === 0)) {
                    continue; // would overlap a finder pattern
                }
                for ($dy = -2; $dy <= 2; $dy++) {
                    for ($dx = -2; $dx <= 2; $dx++) {
                        $this->set($x + $dx, $y + $dy, max(abs($dx), abs($dy)) !== 1);
                    }
                }
            }
        }

        $this->drawFormatBits(0);
        $this->drawVersionBits();
    }

    private function drawFinder(int $x, int $y): void
    {
        for ($dy = -4; $dy <= 4; $dy++) {
            for ($dx = -4; $dx <= 4; $dx++) {
                $distance = max(abs($dx), abs($dy));
                $this->set($x + $dx, $y + $dy, $distance !== 2 && $distance !== 4);
            }
        }
    }

    private function drawFormatBits(int $mask): void
    {
        $data = self::FORMAT_BITS[$this->level] << 3 | $mask;
        $rest = $data;
        for ($i = 0; $i < 10; $i++) {
            $rest = ($rest << 1) ^ (($rest >> 9) * 0x537);
        }
        $bits = ($data << 10 | $rest) ^ 0x5412;

        $bit = static fn(int $index): bool => (($bits >> $index) & 1) !== 0;

        for ($i = 0; $i <= 5; $i++) {
            $this->set(8, $i, $bit($i));
        }
        $this->set(8, 7, $bit(6));
        $this->set(8, 8, $bit(7));
        $this->set(7, 8, $bit(8));
        for ($i = 9; $i < 15; $i++) {
            $this->set(14 - $i, 8, $bit($i));
        }

        for ($i = 0; $i < 8; $i++) {
            $this->set($this->size - 1 - $i, 8, $bit($i));
        }
        for ($i = 8; $i < 15; $i++) {
            $this->set(8, $this->size - 15 + $i, $bit($i));
        }
        $this->set(8, $this->size - 8, true); // the dark module
    }

    private function drawVersionBits(): void
    {
        if ($this->version < 7) {
            return;
        }
        $rest = $this->version;
        for ($i = 0; $i < 12; $i++) {
            $rest = ($rest << 1) ^ (($rest >> 11) * 0x1F25);
        }
        $bits = $this->version << 12 | $rest;

        for ($i = 0; $i < 18; $i++) {
            $dark = (($bits >> $i) & 1) !== 0;
            $a    = $this->size - 11 + $i % 3;
            $b    = intdiv($i, 3);
            $this->set($a, $b, $dark);
            $this->set($b, $a, $dark);
        }
    }

    /**
     * @param array<int, int> $codewords
     */
    private function drawCodewords(array $codewords): void
    {
        $bits = [];
        foreach ($codewords as $codeword) {
            for ($bit = 7; $bit >= 0; $bit--) {
                $bits[] = (($codeword >> $bit) & 1) !== 0;
            }
        }
        $bits = array_merge($bits, array_fill(0, self::REMAINDER_BITS[$this->version - 1], false));
        $total = count($bits);
        $index = 0;

        for ($right = $this->size - 1; $right >= 1; $right -= 2) {
            if ($right === 6) {
                $right = 5; // the vertical timing pattern
            }
            for ($vertical = 0; $vertical < $this->size; $vertical++) {
                for ($column = 0; $column < 2; $column++) {
                    $x = $right - $column;
                    $y = (($right + 1) & 2) === 0 ? $this->size - 1 - $vertical : $vertical;
                    if (!$this->reserved[$y][$x] && $index < $total) {
                        $this->modules[$y][$x] = $bits[$index];
                        $index++;
                    }
                }
            }
        }
    }

    private function drawBestMask(): void
    {
        $best = 0;
        $score = PHP_INT_MAX;

        for ($mask = 0; $mask < 8; $mask++) {
            $this->applyMask($mask);
            $this->drawFormatBits($mask);
            $penalty = $this->penalty();
            if ($penalty < $score) {
                $score = $penalty;
                $best  = $mask;
            }
            $this->applyMask($mask); // toggling twice restores the modules
        }

        $this->applyMask($best);
        $this->drawFormatBits($best);
    }

    private function applyMask(int $mask): void
    {
        for ($y = 0; $y < $this->size; $y++) {
            for ($x = 0; $x < $this->size; $x++) {
                if ($this->reserved[$y][$x]) {
                    continue;
                }
                $invert = match ($mask) {
                    0 => ($x + $y) % 2 === 0,
                    1 => $y % 2 === 0,
                    2 => $x % 3 === 0,
                    3 => ($x + $y) % 3 === 0,
                    4 => (intdiv($y, 2) + intdiv($x, 3)) % 2 === 0,
                    5 => ($x * $y) % 2 + ($x * $y) % 3 === 0,
                    6 => (($x * $y) % 2 + ($x * $y) % 3) % 2 === 0,
                    7 => (($x * $y) % 3 + ($x + $y) % 2) % 2 === 0,
                };
                if ($invert) {
                    $this->modules[$y][$x] = !$this->modules[$y][$x];
                }
            }
        }
    }

    private function penalty(): int
    {
        $penalty = 0;
        $size    = $this->size;

        // runs of the same colour, in rows and columns
        for ($i = 0; $i < $size; $i++) {
            $rowRun = 1;
            $colRun = 1;
            for ($j = 1; $j < $size; $j++) {
                $rowRun = $this->modules[$i][$j] === $this->modules[$i][$j - 1] ? $rowRun + 1 : 1;
                $colRun = $this->modules[$j][$i] === $this->modules[$j - 1][$i] ? $colRun + 1 : 1;
                if ($rowRun === 5) {
                    $penalty += 3;
                } elseif ($rowRun > 5) {
                    $penalty++;
                }
                if ($colRun === 5) {
                    $penalty += 3;
                } elseif ($colRun > 5) {
                    $penalty++;
                }
            }
        }

        // blocks of the same colour
        for ($y = 1; $y < $size; $y++) {
            for ($x = 1; $x < $size; $x++) {
                $module = $this->modules[$y][$x];
                if ($module === $this->modules[$y - 1][$x]
                    && $module === $this->modules[$y][$x - 1]
                    && $module === $this->modules[$y - 1][$x - 1]
                ) {
                    $penalty += 3;
                }
            }
        }

        // finder like patterns, also counted with the light area on either side
        $finder = [true, false, true, true, true, false, true, false, false, false, false];
        $reversed = array_reverse($finder);
        for ($i = 0; $i < $size; $i++) {
            for ($j = 0; $j + 11 <= $size; $j++) {
                $row = [];
                $column = [];
                for ($k = 0; $k < 11; $k++) {
                    $row[] = $this->modules[$i][$j + $k];
                    $column[] = $this->modules[$j + $k][$i];
                }
                foreach ([$finder, $reversed] as $pattern) {
                    if ($row === $pattern) {
                        $penalty += 40;
                    }
                    if ($column === $pattern) {
                        $penalty += 40;
                    }
                }
            }
        }

        // share of dark modules
        $dark = 0;
        foreach ($this->modules as $row) {
            foreach ($row as $module) {
                if ($module) {
                    $dark++;
                }
            }
        }
        $total = $size * $size;
        $penalty += intdiv(abs($dark * 20 - $total * 10), $total) * 10;

        return $penalty;
    }

    private function set(int $x, int $y, bool $dark): void
    {
        // the finder patterns reach one ring outside the grid (the quiet area)
        if ($x < 0 || $y < 0 || $x >= $this->size || $y >= $this->size) {
            return;
        }
        $this->modules[$y][$x]  = $dark;
        $this->reserved[$y][$x] = true;
    }
}
