<?php
namespace App\Traits;

trait GlobalFunctions
{

    public function formatPhoneNumber($phone_no)
    {
        $prefix = substr($phone_no, 0, 2);

        if ($prefix == '07' || $prefix == '01') {

            if ($prefix == '07') {
                $str = $str = explode("7", $phone_no, 2);

                if (!empty($str[1])) {
                    if (strlen($str[1]) == 8) {
                        $recipient = "2547" . $str[1];
                        $resp = $recipient;
                    } else {
                        $resp = "Invalid"; //Invalid Phone number
                    }
                } else {
                    $resp = "Invalid"; //Invalid Phone number
                }
            } else {
                $str = $str = explode("1", $phone_no, 2);

                if (!empty($str[1])) {
                    if (strlen($str[1]) == 8) {
                        $recipient = "2541" . $str[1];
                        $resp = $recipient;
                    } else {
                        $resp = "Invalid"; //Invalid Phone number
                    }
                } else {
                    $resp = "Invalid"; //Invalid Phone number
                }
            }

        } else {
            $resp = "Invalid"; //Invalid Phone number
        }

        return $resp;
    }
}
