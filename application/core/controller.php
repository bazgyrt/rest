<?php

namespace core;

class Controller {
	
	public $model;
	public $view;

	function __construct()
	{
		//$this->view = new View();
	}

    function behaviors()
    {
        return array();
    }

    function success($data)
    {
        if (is_array($data)) {
            foreach($data as $key => $d) {
                $data[$key] = filter_var($d, FILTER_SANITIZE_STRING);
            }
        } else {
            $data = filter_var($data, FILTER_SANITIZE_STRING);
        }

        echo json_encode(array('success' => true, 'data' => $data));
    }

    function fail($data)
    {
        echo json_encode(array('success' => false, 'data' => $data));
    }
}
