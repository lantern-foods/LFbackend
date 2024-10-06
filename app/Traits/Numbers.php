<?php
	namespace App\Traits;

	trait Numbers {

		/**
         * Remove commas(,) from amount
         */
        public function clean_monetary_value($value){
        	
            $new_value=  str_replace(",", "", $value);
            
            return $new_value;
        }

         /*
         * Generate unique random string
         *
         * @return string
         */
        public function generaterandomString($length) {

            $chars = "abcdefghijkmnopqrstuvwxyz023456789";

            srand((double)microtime()*1000000);

            $i = 0;

            $randStr = '' ;

            while ($i <= $length) {

                $num = rand() % 33;

                $tmp = substr($chars, $num, 1);

                $randStr = $randStr . $tmp;

                $i++;

            }

            return $randStr;
        }    
	}