<?php

namespace app\classes;

class Html
{
    public static function formatDate($date, $stringFormat = 'Y-m-d H:i:s')
    {
        $dt = \DateTime::createFromFormat($stringFormat, $date);

        if ($dt)
        {
            return $dt->format('m/d/Y'); 
        }

        return null;
    }

    public static function selected($elementValue, $targetValue = true)
    {
        if ($elementValue == $targetValue)
        {
            return 'selected';
        }

        return null;
    }

    public static function checked($elementValue, $targetValue = true)
    {
        if ($elementValue == $targetValue)
        {
            return 'checked';
        }

        return null;
    }
}