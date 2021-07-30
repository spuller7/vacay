<?php

namespace app\core\form;
use app\core\Model;

class Form
{
    public static function start($id, $action, $method, $class = '')
    {
        echo sprintf('<form id="%s" action="%s" method="%s" class="'.$class.'">', $id, $action, $method);
        return new Form();
    }

    public static function end()
    {
        echo '</form>';
    }

    public function field(Model $model, $attribute)
    {
        return new Field($model, $attribute);
    }
}