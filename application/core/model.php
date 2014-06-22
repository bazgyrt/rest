<?php

namespace core;

class Model
{
    protected $db;
    public  $errors = array();
    private $props = array();
    protected $table;

    function __construct()
    {
        $conn = require("config/db.php");

        $this->db = new \PDO("mysql:dbname=$conn[dbname];host=$conn[server]", $conn['user'], $conn['password']);
        $this->db->query("set names utf8");
        $this->setTableName($this->_getTableName());
        $this->getClassParametrs();

    }

    public function rules()
    {
        return array();
    }

    public function setTableName($name)
    {
        $this->table = $name;
    }

    public function getTableName()
    {
        return $this->table;
    }

    public function getAll()
    {
        //$table = $this->getTableName();
        $sql = "select * from {$this->table}";
        $data = $this->db->query($sql);

        return $data->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function load()
    {
        foreach($this->props as $prop) {
            if (isset($_REQUEST[$prop])) {
                $this->$prop = $_REQUEST[$prop];
            }
        }
        return true;
    }

    public function validate($action)
    {
        $rules = $this->rules();

        if (!empty($rules)) {
            foreach($rules as $rule) {
                $validator = new $rule['class'];

                $this->errors = array_merge($this->errors, $validator->validate($action, $_REQUEST, $rule['params']));
            }
            if (!empty($this->errors)) return false;
        }
        return true;
    }

    public function getProps()
    {
        return $this->props;
    }

    public function getPropsWithValue()
    {
        $array = array();
        foreach($this->props as $property) {
            $array[$property] = $this->$property;
        }
        return $array;
    }

    public function getProperty($property)
    {
        return $this->$property;
    }

    public function insert()
    {
        $values = $this->getPropsWithValue();

        $sql = "insert into {$this->table}";

        $cols = array(); // fields names array
        $val = array(); // fields value array
        foreach($values as $key => $value) {
            array_push($cols, $key);
            array_push($val, "'$value'");
        }

        $sql .= "(" . implode(", ", $cols) . ") ";
        $sql .= "values (" . implode(", ", $val) . ")";

        $statement = $this->db->prepare($sql);
        if($statement !== false) {
            if (!$this->db->exec($sql)) {
                array_push($this->errors, "Произошла ошибка при записи в БД");
                return false;
            } else return true;
        }
    }

    public function update($array, $where = array())
    {
        $sql = "UPDATE $this->table set ";

        $sql .= $this->makeString($array);

        if (!empty($where)) {
            $sql .= " where " . $this->makeString($where);
        }

        $this->db->exec($sql);
    }

    public function selectOne($where = array(), $fields = array())
    {
        if (!empty($fields)) {
            $fields = implode(", ", $fields);
        } else $fields = "* ";

        $sql = "select $fields from $this->table ";

        if (!empty($where)) $sql .= "where " . $this->makeString($where, " and ");

        $data = $this->db->query($sql);


        return $data->fetch(\PDO::FETCH_ASSOC);
    }

    protected function makeString($array, $separator = ", ")
    {
        $fields = array();
        foreach($array as $key => $value) {
            $fields[] = "$key='" . $value . "'";
        }
        return implode($separator, $fields);
    }

    protected function getClassParametrs()
    {
        $reflect = new \ReflectionClass($this);

        $props = $reflect->getProperties(\ReflectionProperty::IS_PROTECTED);

        foreach ($props as $prop) {
            if ($prop->class === get_called_class()) {
                array_push($this->props, $prop->getName());
            }
        }

    }

    private function _getTableName()
    {
        $class = strtolower(get_called_class());

        preg_match("/model_(\w+)/", $class, $matches);

        return $matches[1];
    }

}