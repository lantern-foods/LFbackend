<?php
namespace App\Traits;

trait Numbers
{
    /**
     * Remove commas(,) from a monetary value.
     *
     * @param string $value
     * @return string
     */
    public function cleanMonetaryValue(string $value): string
    {
        return str_replace(",", "", $value);
    }

    /**
     * Generate a unique random string of specified length.
     *
     * @param int $length
     * @return string
     */
    public function generateRandomString(int $length): string
    {
        $chars = "abcdefghijkmnopqrstuvwxyz023456789";
        $charLength = strlen($chars);
        $randomStr = '';

        for ($i = 0; $i < $length; $i++) {
            $randomStr .= $chars[random_int(0, $charLength - 1)];
        }

        return $randomStr;
    }
}
