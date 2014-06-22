<?php

namespace core\filters;

class RequestFilter
{
    private $method;
    private $actionName;
    private $params;

    function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'];
    }

    public function setParams($config, $action)
    {
        $this->params = $config;
        $this->setActionName($action);
    }

    public function setActionName($action)
    {
        preg_match("/action_(\w+)/", $action, $matches);

        $this->actionName = $matches[1];
    }

    public function filter()
    {
        foreach($this->params as $action => $param) {
            if ($this->actionName === $action) {
                if (isset($param[$this->method]) && !empty($param[$this->method])) {
                    return $param[$this->method];
                }
            }
        }
        return "action_{$this->actionName}";
    }
}