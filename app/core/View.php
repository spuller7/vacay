<?php

namespace app\core;

/**
 * Class View
 */
class View
{
    public string $title = '';

    /**
     * renderView
     * 
     * Get the requested view file content and any additional components relevant to the page
     *
     * @param string $view
     * @param array $params
     * @return string html
     */
    public function renderView($view, $params = []): string
    {
        // Only show navigation html for views in the 'pages' folder
        $this->show_navigation = str_starts_with($view, "pages/");

        // Requested view file content
        $viewContent = $this->renderOnlyView($view, $params, false);

        // Always include the header for every page
        $headerContent = $this->headerContent();
        
        return str_replace('{{content}}', $viewContent, $headerContent);
    }

    public function renderComponent($view, $params = [], $as_string = false)
    {
        return $this->renderOnlyView("components/$view", $params, $as_string);
    }

    /**
     * Header Content
     * 
     * fetches the header html in the view file
     *
     * @return string
     */
    protected function headerContent()
    {
        ob_start();
        include_once Application::$ROOT_DIR.'/app/views/layouts/head.php';
        return ob_get_clean();
    }

    /**
     * renderOnlyView
     * 
     * Fetches requested view file content only
     *
     * @param string $view
     * @return void
     */
    protected function renderOnlyView($view, $params, $as_string)
    {
        foreach ($params as $key => $value)
        {
            $$key = $value;
        }

        ob_start();

        if ($as_string)
        {
            require(Application::$ROOT_DIR.'/app/views/'.$view.'.php');
            $return = ob_get_contents();
            ob_get_clean();
            return $return;
        }

        require Application::$ROOT_DIR.'/app/views/'.$view.'.php';
        return ob_get_clean();
    }
} 