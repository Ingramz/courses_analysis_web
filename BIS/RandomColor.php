<?php
namespace BIS;

class RandomColor {
    public static function get($qty) {
        $primary = array('00', '33', '66', '99', 'cc', 'ff');
        $colors = array();
        for ($i = 0; $i < $qty; $i++) {
            $color = '#';
            for ($c = 0; $c < 3; $c++) {
                $t = rand(0, 5);
                $color .= $primary[$t];
            }
            $colors[] = $color;
        }
        return $colors;
    }
}
