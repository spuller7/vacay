<?php

namespace app\core;

class Datatable {
    
    protected $data, $columns;

    public function __construct($data, $columns)
    {
        $this->data = $data;
        $this->columns = $columns;
    }

    public function generate()
    {
        $items = array();

        foreach($this->data as $row)
        {
            $item = array();
            foreach($this->columns as $column)
            {
                if (isset($column['formatter']))
                {
                    $item[$column['dt']] = $this->clean_row($column['formatter']($row[$column['db']], $row));
                }
                else
                {
                    $item[$column['dt']] = $row[$column['db']];
                }
            }

            $items[] = $item;
        }

        return $items;
    }
    
    protected function clean_row($data)
    {
        $data = str_replace(array("\n", "\r", "\t"), '', $data);
        return $data;
    }
}