<?php
#
#
# This file originated with https://github.com/cicirello/user-statistician
# 
# Copyright (c) 2021-2022 Vincent A Cicirello
# https://www.cicirello.org/
#
# MIT License
# 
# Permission is hereby granted, free of charge, to any person obtaining a copy
# of this software and associated documentation files (the "Software"), to deal
# in the Software without restriction, including without limitation the rights
# to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
# copies of the Software, and to permit persons to whom the Software is
# furnished to do so, subject to the following conditions:
# 
# The above copyright notice and this permission notice shall be included in all
# copies or substantial portions of the Software.
# 
# THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
# IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
# FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
# AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
# LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
# OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
# SOFTWARE.
#

/*
* The dict that follows is derived from
* default-widths.json from
* https://github.com/google/pybadges,
* which is licensed under Apache-2.0.
 */

include_once 'dict.php';

class Text
{
    /**
     *  Calculates the length of a string in DejaVu Sans for a specified font size.
     * 
     * 
     * @param string $s The string.
     * @param float $size The font size.
     * @param bool $pixels If True, the size is in px, otherwise it is in pt.
     * @param int $fontWeight The weight of the font (e.g., 400 for normal, 600 for bold, etc)
     */
    public function  calculateTextLength(string $s, float $size, bool $pixels, int $fontWeight)
    {

        if ($pixels) {
            $size *= 0.75;
        }
        $weightMultiplier = 1;
        if ($fontWeight != 400) {
            $weightMultiplier = $fontWeight / 400;
        }

        return $weightMultiplier * $this->calculateTextLength110(s);
    }
    /**
     *  Calculates the length of a string in DejaVu Sans 110pt font, factoring in font weight.
     * 
     * 
     * @param string $s The string.
     * @param int $fontWeight The weight of the font (e.g., 400 for normal, 600 for bold, etc)
     */
    public function  calculateTextLength110Weighted(string $s, int $fontWeight)
    {
        $weightMultiplier = 1;
        if ($fontWeight != 400) {
            $weightMultiplier = $fontWeight / 400;
        }
        return $weightMultiplier * $this->calculateTextLength110(s);
    }

    /**
     *  Calculates the length of a string in DejaVu Sans 110pt font.
     * 
     * @param string $s The string.
     */
    public function  calculateTextLength110(string $s)
    {

        if ($s == null || strlen($s) == 0) {
            return 0;
        }
        $total = 0;
        $len = mb_strlen($s);
        for ($i = 0; $i < $len; $i++) {
            $c = mb_substr($s, $i, 1);
            if (array_key_exists($c, Dict::$defaultWidths["character-lengths"])) {
                $total += Dict::$defaultWidths["character-lengths"][$c];
            } else {
                $total += Dict::$defaultWidths["mean-character-length"];
            }
            if ($i > 0) {
                $pair = mb_substr($s, $i - 1, 2, 'UTF-8');
                if (array_key_exists($pair, Dict::$defaultWidths["kerning-pairs"])) {
                    $total -= Dict::$defaultWidths["kerning-pairs"][$pair];
                }
            }
        }
        return $total;
    }
}
