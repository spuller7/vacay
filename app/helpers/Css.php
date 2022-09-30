<?php

class Css
{
    public static function load($path, $output = true)
    {
        $file_path = "public/css/".$path.".css";
        if (!file_exists($file_path))
        {
            //return null;
        }

        $script = "<link rel='stylesheet' type='text/css' media='all' href='".$file_path."'/>\r\n";

        if ($output)
        {
            echo($script);
        }
        else
        {
            return $script;
        }
    }

    public static function loadAll($paths, $output = true)
    {
        $html = array();

        if (!is_array($paths))
        {
            return false;
        }

        foreach($paths as $path)
        {
            $html[] = static::load($path, false);
        }

        if ($output)
        {
            echo(join("\r\n", $html));
        }
        else
        {
            return $html;
        }
    }
}