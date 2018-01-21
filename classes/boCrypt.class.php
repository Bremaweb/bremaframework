<?php

/**
 * Class boCrypt
 * A very basic key based encryption class
 */

class boCrypt {
    private $_key = null;

    public function __construct($key){
        $this->_key = $key;
    }

    /**
     * @param string $data
     * @param int $saltLevel
     * @return string
     */
    public function encrypt($data, $saltLevel = 30){
        $k = 0;
        $sK = str_split($this->_key,1);

        $data = $this->salt($data, $saltLevel);
        $data = str_split($data,1);

        $nData = "";
        foreach ( $data as $c ){
            $kN = ord($sK[$k]);
            $chN = ord($c);
            $nData .= chr($this->math($chN, $kN));
            $k++;
            if ( $k > (count($sK) - 1) ){
                $k = 0;
            }
        }

        return base64_encode($nData);
    }

    /**
     * @param string $data
     * @return string
     */
    public function decrypt($data){
        $k = 0;
        $sK = str_split($this->_key,1);

        $data = base64_decode($data);
        $data = str_split($data,1);

        $nData = "";
        foreach ( $data as $c ){
            $nData .= chr($this->unmath(ord($c), ord($sK[$k])));
            $k++;
            if ( $k > (count($sK) - 1) ){
                $k = 0;
            }
        }

        $nData = $this->unsalt($nData);
        return $nData;
    }

    /**
     * @param string $data
     * @param int $level
     * @return string
     */
    private function salt($data, $level){
        if ( $level < 10 ){
            $level = 10;
        }
        if ( $level > 100 ){
            $level = 100;
        }
        $saltLength = rand(10,(10 + floor($level / 2)));
        $saltCharOptions = str_split(count_chars($data,4),1);

        if ( count($saltCharOptions) > 1){
            $saltChars = array();
            shuffle($saltCharOptions);
            for ( $s = 0; $s < $saltLength; $s++ ){
                $pick = $saltCharOptions[rand(0,count($saltCharOptions)-1)];
                // null can't be one of the salt characters
                if ( ord($pick) != 0 ){
                    $saltChars[] = $pick;
                } else {
                    $s--;
                }
            }
        } else {
            return $data;
        }

        $data = str_split($data,rand(1,5));
        $nData = implode('', $saltChars) . chr(0);
        foreach ( $data as $c ){
            $nData .= $c;
            if ( rand(0,100) < $level ){
                for ( $sc = 0; $sc < rand(1,4); $sc++ ){
                    $nData .= $saltChars[rand(0,count($saltChars)-1)];
                }
            }
        }

        return $nData;
    }

    /**
     * @param string $data
     * @return string
     */
    private function unsalt($data){
        $nullPos = strpos($data,chr(0));
        if ( $nullPos !== false && $nullPos > 9 && $nullPos < 61 ){
            $saltChars = str_split(substr($data,0,$nullPos),1);
            $data = substr($data,$nullPos+1);
            return str_replace($saltChars,'',$data);
        } else {
            return $data;
        }
    }

    /**
     * @param int $char
     * @param int $keyChar
     * @return int
     */
    private function math($char, $keyChar){
        return $char + ($keyChar + 127 + (255 % $keyChar));
    }

    /**
     * @param int $char
     * @param int $keyChar
     * @return int
     */
    private function unmath($char, $keyChar){
        return $char - ($keyChar + 127 + (255 % $keyChar));
    }

}