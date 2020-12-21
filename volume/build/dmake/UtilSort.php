<?php
/**
 * MIT License
 * (c) 2007 - 2019 Heinrich Stamerjohanns
 *
 */

namespace Dmake;

class UtilSort
{
    /**
     * multi sort an associated array
     */
    public static function sortByKey(array $array, string $key, string $order = ''): array
    {
        if ($order == "DESC") {
            $cmp = function ($a, $b) use ($key) {
                return (($a[$key] < $b[$key]) ? 1 : (($a[$key] == $b[$key]) ? 0 : -1));
            };
        } else {
            $cmp = function ($a, $b) use ($key) {
                return (($a[$key] > $b[$key]) ? 1 : (($a[$key] == $b[$key]) ? 0 : -1));
            };
        }
        uasort($array, $cmp);
        return $array;
    }
}
