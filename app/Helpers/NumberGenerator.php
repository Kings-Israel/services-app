<?php

namespace App\Helpers;

class NumberGenerator
{
    public static function generateUniqueNumber($model, $column, $min = 1000, $max = 9999)
    {
        $number = mt_rand($min, $max);

        if (self::numberExists($model, $column, $number)) {
            return self::generateUniqueNumber($model, $column);
        }

        return $number;
    }

    private static function numberExists($model, $column, $number)
    {
        return $model::where($column, $number)->exists();
    }
}
