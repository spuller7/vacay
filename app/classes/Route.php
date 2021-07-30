<?php

namespace app\classes;

class Route
{
    public $label = '';
    public $path = '';
    public $route = null;
    public $icon = null;
    public $permission = null;

    function __construct($label, $path, $route, $icon = '', $permission = null)
    {
        $this->label = $label;
        $this->path = $path;
        $this->route = $route;
        $this->icon = $icon;
        $this->permission = $permission;
    }
}