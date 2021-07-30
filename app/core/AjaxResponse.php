<?php

namespace app\core;

class AjaxResponse
{
    private $result = null;
    private $message = null;
    protected $data = array();

    public function setResult($result, $message)
    {
        $this->result = $result;
        $this->message = $message;
    }

    public function setData($dataName, $dataValue)
    {
        $this->data[$dataName] = $dataValue;
    }

    public function setDataObject(array $data)
    {
        $this->data = $data;
    }

    public function __set($name, $value)
    {
        return $this->setData($name, $value);
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->data))
        {
            return $this->data[$name];
        }
        else
        {
            return null;
        }
    }

    public function getData()
    {
        return $this->data;
    }

    public function utf8ize($d) {
        if (is_array($d)) {
            foreach ($d as $k => $v) {
                $d[$k] = $this->utf8ize($v);
            }
        } else if (is_string ($d)) {
            return utf8_encode($d);
        }
        return $d;
    }

    public function getJson()
    {
        if ($this->result)
        {
            $this->data['result'] = $this->result;
        }

        if ($this->message)
        {
            $this->data['message'] = $this->message;
        }
        
        return json_encode($this->utf8ize($this->data));
    }

    public function send()
    {
        if (ob_get_contents())
        {
            ob_clean();
        }

        if (!headers_sent())
        {
            header('Content-type: application/json');
        }

        echo $this->getJson();
    }
}