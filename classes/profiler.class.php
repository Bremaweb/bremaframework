<?php


class profiler {
    private static $_timers = array();

    public static function startTimer($name, $addlData = ''){
        if ( empty(static::$_timers[$name]) ){
            static::$_timers[$name] = array(
                'start' => 0,
                'end' => 0,
                'duration' => 0,
                'count' => 0,
                'data' => $addlData
            );
        }

        static::$_timers[$name]['start'] = microtime(true);
        static::$_timers[$name]['count']++;
    }

    public static function stopTimer($name){
        if ( !empty(static::$_timers[$name]) ){
            static::$_timers[$name]['end'] = microtime(true);

            $d = static::$_timers[$name]['end'] - static::$_timers[$name]['start'];
            static::$_timers[$name]['duration'] += $d;
        }
    }

    public static function getTimers(){
        return static::$_timers;
    }

    public static function writeProfilerData(){
        if ( !empty(static::$_timers) ){
            $data = date("Y-m-d H:i:s") . "\r\n";

            foreach ( static::$_timers as $name => $timer ){
                $data .= $name . "\r\n";
                if ( !empty($timer['data']) ){
                    $data .= $timer['data'] . "\r\n";
                }
                $data .= str_repeat("=", 15) . "\r\n";
                foreach ( $timer as $field => $value ){
                    if ( $field == 'count' || $field == 'duration' ){
                        $data .= "  " . $field . ": " . $value . "\r\n";
                    }
                }
                $data .= " Avg: " . round($timer['duration'] / $timer['count'], 6) . "\r\n";
                $data .= "\r\n";
            }

            $file = APP_DIR . 'logs/profiler.txt';
            $h = fopen($file, "a+");
                fwrite($h, $data);
            fclose($h);
        }
    }
}