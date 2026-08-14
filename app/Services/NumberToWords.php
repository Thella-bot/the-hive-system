<?php

namespace App\Services;

class NumberToWords
{
    public static function convert(float $number): string
    {
        $amount = number_format($number, 2);
        $parts = explode('.', $amount);
        $dollars = (int) $parts[0];
        $cents = (int) $parts[1];

        if ($dollars == 0 && $cents == 0) {
            return 'Zero';
        }

        $result = '';
        if ($dollars > 0) {
            $result .= self::convertChunk($dollars);
        }
        if ($cents > 0) {
            $result .= ' and ' . self::convertChunk($cents) . ' cents';
        }

        return trim($result);
    }

    private static function convertChunk(int $num): string
    {
        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
        $teens = ['Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];

        if ($num < 10) {
            return $ones[$num];
        }
        if ($num < 20) {
            return $teens[$num - 10];
        }
        if ($num < 100) {
            return $tens[(int) floor($num / 10)] . ($num % 10 ? ' ' . $ones[$num % 10] : '');
        }
        if ($num < 1000) {
            return $ones[(int) floor($num / 100)] . ' Hundred' . ($num % 100 ? ' ' . self::convertChunk($num % 100) : '');
        }
        if ($num < 1000000) {
            return self::convertChunk((int) floor($num / 1000)) . ' Thousand' . ($num % 1000 ? ' ' . self::convertChunk($num % 1000) : '');
        }
        if ($num < 1000000000) {
            return self::convertChunk((int) floor($num / 1000000)) . ' Million' . ($num % 1000000 ? ' ' . self::convertChunk($num % 1000000) : '');
        }

        return 'Number too large';
    }
}
