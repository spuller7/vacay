<?php

class Javascript
{
    public static function load($path, $output = true)
    {
        $file_path = "/js/".$path.".js";

        if (!file_exists($file_path))
        {
            //return null;
        }

        $script = "<script type='text/javascript' src='".$file_path."'></script>\r\n";

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
            return true;
        }
        else
        {
            return $html;
        }
    }
}