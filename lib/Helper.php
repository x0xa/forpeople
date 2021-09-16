<?php

namespace ForPeople;

class Helper {
    static function sha_hash($data): int{
        return self::bchexdec(mb_substr(sha1($data), 0, 16));
    }

    static function bchexdec($hex): int {
        $dec = 0;
        $len = mb_strlen($hex);
        for($i = 1; $i <= $len; $i++)
            $dec = bcadd($dec, bcmul(strval(hexdec($hex[$i - 1])), bcpow('16', strval($len - $i))));

        return intval($dec);
    }

    static function prepareExcerpt($text): string {
        $text = strip_tags($text);

        return Helper::trimText($text);
    }

    static function trimText(string $text, int $maxChars = 200, string $end = '...'): string {
        if (strlen($text) > $maxChars || $text == '') {
            $words = preg_split('/\s/', $text);
            $output = '';
            $i      = 0;
            while (1) {
                $length = strlen($output)+strlen($words[$i]);
                if ($length > $maxChars) {
                    break;
                }
                else {
                    $output .= " " . $words[$i];
                    ++$i;
                }
            }
            $output .= $end;
        }
        else {
            $output = $text;
        }

        return $output;
    }
}