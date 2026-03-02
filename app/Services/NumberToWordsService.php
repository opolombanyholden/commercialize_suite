<?php

namespace App\Services;

class NumberToWordsService
{
    protected array $units = [
        '', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf',
        'dix', 'onze', 'douze', 'treize', 'quatorze', 'quinze', 'seize', 'dix-sept', 'dix-huit', 'dix-neuf'
    ];

    protected array $tens = [
        '', '', 'vingt', 'trente', 'quarante', 'cinquante', 'soixante', 'soixante', 'quatre-vingt', 'quatre-vingt'
    ];

    /**
     * Convertir un nombre en lettres (français)
     *
     * @param float $number Le nombre à convertir
     * @param string $currency La devise (défaut: FCFA)
     * @return string Le nombre en lettres
     */
    public function convert(float $number, string $currency = 'FCFA'): string
    {
        if ($number == 0) {
            return 'zéro ' . $currency;
        }

        $number = abs($number);
        $intPart = floor($number);
        $decPart = round(($number - $intPart) * 100);

        $result = $this->convertInteger($intPart);

        if ($currency === 'FCFA') {
            $result .= ' francs CFA';
        } else {
            $result .= ' ' . $currency;
        }

        if ($decPart > 0) {
            $result .= ' et ' . $this->convertInteger($decPart) . ' centimes';
        }

        return ucfirst($result);
    }

    /**
     * Convertir un entier en lettres
     */
    protected function convertInteger(int $number): string
    {
        if ($number < 20) {
            return $this->units[$number];
        }

        if ($number < 100) {
            return $this->convertTens($number);
        }

        if ($number < 1000) {
            return $this->convertHundreds($number);
        }

        if ($number < 1000000) {
            return $this->convertThousands($number);
        }

        if ($number < 1000000000) {
            return $this->convertMillions($number);
        }

        return $this->convertBillions($number);
    }

    /**
     * Convertir les dizaines (20-99)
     */
    protected function convertTens(int $number): string
    {
        $ten = floor($number / 10);
        $unit = $number % 10;

        // Cas spéciaux pour 70-79 et 90-99
        if ($ten == 7 || $ten == 9) {
            $unit += 10;
        }

        $result = $this->tens[$ten];

        if ($unit > 0) {
            // Liaison avec "et" pour 21, 31, 41, 51, 61, 71
            if ($unit == 1 && $ten != 8 && $ten != 9) {
                $result .= ' et ';
            } elseif ($ten == 8 && $unit > 0) {
                $result .= '-';
            } else {
                $result .= '-';
            }
            $result .= $this->units[$unit];
        } elseif ($ten == 8) {
            $result .= 's'; // quatre-vingts
        }

        return $result;
    }

    /**
     * Convertir les centaines (100-999)
     */
    protected function convertHundreds(int $number): string
    {
        $hundred = floor($number / 100);
        $remainder = $number % 100;

        if ($hundred == 1) {
            $result = 'cent';
        } else {
            $result = $this->units[$hundred] . ' cent';
        }

        if ($remainder > 0) {
            $result .= ' ' . $this->convertInteger($remainder);
        } elseif ($hundred > 1) {
            $result .= 's'; // deux cents, trois cents, etc.
        }

        return $result;
    }

    /**
     * Convertir les milliers (1000-999999)
     */
    protected function convertThousands(int $number): string
    {
        $thousand = floor($number / 1000);
        $remainder = $number % 1000;

        if ($thousand == 1) {
            $result = 'mille';
        } else {
            $result = $this->convertInteger($thousand) . ' mille';
        }

        if ($remainder > 0) {
            $result .= ' ' . $this->convertInteger($remainder);
        }

        return $result;
    }

    /**
     * Convertir les millions
     */
    protected function convertMillions(int $number): string
    {
        $million = floor($number / 1000000);
        $remainder = $number % 1000000;

        if ($million == 1) {
            $result = 'un million';
        } else {
            $result = $this->convertInteger($million) . ' millions';
        }

        if ($remainder > 0) {
            $result .= ' ' . $this->convertInteger($remainder);
        }

        return $result;
    }

    /**
     * Convertir les milliards
     */
    protected function convertBillions(int $number): string
    {
        $billion = floor($number / 1000000000);
        $remainder = $number % 1000000000;

        if ($billion == 1) {
            $result = 'un milliard';
        } else {
            $result = $this->convertInteger($billion) . ' milliards';
        }

        if ($remainder > 0) {
            $result .= ' ' . $this->convertInteger($remainder);
        }

        return $result;
    }

    /**
     * Raccourci statique pour conversion rapide
     */
    public static function toWords(float $number, string $currency = 'FCFA'): string
    {
        return (new self())->convert($number, $currency);
    }
}
