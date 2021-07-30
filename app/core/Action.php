<?php

namespace app\core;

class Action
{
    protected $text = null;
    protected $icon = null;
    protected $show = true;
    protected $class = array();
    protected $data_attrs = array();
    protected $url = 'javascript:void(0)';
    protected $title = null;
    protected $target = null;

    public static function create($text, $icon)
    {
        $a = new Action();
        $a->text = $text;
        $a->icon = $icon;

        return $a;
    }

    public static function generate(array $actions, $as_string = true, $force_ellipsis = false)
    {
        $render_actions = array();

        foreach($actions as $a)
        {
            if ($a->show)
            {
                $render_actions[] = $a;
            }
        }

        $num_actions = count($render_actions);
        if (!$num_actions)
        {
            return null;
        }

        
        if ($num_actions == 1 && !$force_ellipsis)
        {
            $a = $render_actions[0];
            $a->title($a->text)
                ->text(null)
                ->classes('single_action');

            return $a->render();
        }

        return Application::$app->view->renderComponent('actions/ellipsis', array('actions' => $render_actions, 'force_ellipsis' => $force_ellipsis), $as_string);
    }

    public function render()
    {
        return Application::$app->view->renderComponent('actions/action', array('a' => $this));
    }

    /**
     * Data
     * 
     * prepare data attribute for html parameters
     *
     * @param string $name
     * @param mixed $val
     * @return static
     */
    public function data($name, $val)
    {
        $this->data_attrs[$name] = $val;
        return $this;
    }

    /**
     * Classes
     *
     * @param string|array $classes
     * @return static
     */
    public function classes($classes)
    {
        if (!is_array($classes))
        {
            $classes = explode(' ', $classes);
        }

        $this->class = array_merge($this->class, $classes);

        return $this;
    }

    /**
     * Show
     *
     * @param bool $show
     * @return static
     */
    public function show($show)
    {
        $this->show = $show;
        return $this;
    }

    /**
     * Title
     *
     * @param string $title
     * @return static
     */
    public function title($title)
    {
        $this->title = $title;
        return $this;
    }

    public function url($url)
    {
        $this->url = $url;
        return $this;
    }

    public function text($text)
    {
        $this->text = $text;
        return $this;
    }

    public function target($target = '_blank')
    {
        $this->target = $target;
        return $this;
    }

    public function __get($param)
    {
        return $this->{$param};
    }
}