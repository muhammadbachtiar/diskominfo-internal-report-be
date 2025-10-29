<?php

namespace Domain\Report\Services;

class GeoUtils
{
    // Basic geohash implementation (precision 9-12 typical)
    public static function geohash(float $lat, float $lng, int $precision = 9): string
    {
        $base32 = '0123456789bcdefghjkmnpqrstuvwxyz';
        $latInterval = [-90.0, 90.0];
        $lngInterval = [-180.0, 180.0];
        $hash = '';
        $isEven = true;
        $bit = 0;
        $ch = 0;
        while (strlen($hash) < $precision) {
            if ($isEven) {
                $mid = ($lngInterval[0] + $lngInterval[1]) / 2;
                if ($lng > $mid) {
                    $ch |= 1 << (4 - $bit);
                    $lngInterval[0] = $mid;
                } else {
                    $lngInterval[1] = $mid;
                }
            } else {
                $mid = ($latInterval[0] + $latInterval[1]) / 2;
                if ($lat > $mid) {
                    $ch |= 1 << (4 - $bit);
                    $latInterval[0] = $mid;
                } else {
                    $latInterval[1] = $mid;
                }
            }
            $isEven = ! $isEven;
            if ($bit < 4) {
                $bit++;
            } else {
                $hash .= $base32[$ch];
                $bit = 0;
                $ch = 0;
            }
        }
        return $hash;
    }
}

