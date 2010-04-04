<?php
/**
 * Description of Date
 *
 * @author toxa
 */
class Date {
    private static $date_time = null;

    public static function init($timeZone = DateTimeZone::AMERICA){
        if(self::$date_time != null){
            self::$date_time = null;
        }
        self::$date_time = new DateTime('now', $timeZone);
    }

    public static function get($format = 'Y-m-d H:i'){
        return self::$date_time->format($format);
    }
}
?>
