<?php

namespace core\validators;

class BaseValidator
{
    public $valid = true;
    public $errors = array();
    private $params;

    /**
     * Validation function, gets validation rules from model::rules()
     * @param string $action action need to be validate
     * @param array $params parameters from form
     * @param array $actions action rules from model
     * @return array validation errors
     */
    function validate($action, $params, $actions)
    {
        $this->params = $params;

        foreach($actions as $action_name => $fields) {
            $vactions = explode(", ", $action_name);

            if (array_search($action, $vactions) !== false) {
                foreach($fields as $field_name => $rules) {
                    foreach($rules as $rule => $value) {
                        if (method_exists($this, $rule)) {
                            $this->$rule($field_name, $value);
                        }
                    }
                }
            }
        }

        if (!empty($this->errors)) {
            $this->valid = false;
        }

        return $this->errors;
    }

    private function required($field, $rule)
    {
        if($rule) {
            if (empty($this->params[$field])) {
                array_push($this->errors, "Поле $field не может быть пустым!");
            }
        }
    }

    private function pattern($field, $rule)
    {
        if (!empty( $this->params[$field])) {
            if (!preg_match($rule, $this->params[$field])) {
                array_push($this->errors, "Поле $field заполнено не корректно!");
            }
        }
    }

    private function min($field, $rule)
    {
        if (!empty($this->params[$field])) {
            if (strlen($this->params[$field]) < $rule) {
                array_push($this->errors, "Количество символов в поле $field должно быть не менее $rule!");
            }
        }
    }

    private function equal($field, $rule)
    {
        if ($this->params[$field] !== $this->params[$rule]) {
            array_push($this->errors, "Поле $field должно совпадать с полем $rule!");
        }
    }

    private function email($field, $rule)
    {
        if (filter_var($this->params[$field], FILTER_VALIDATE_EMAIL) === false) {
            array_push($this->errors, "Поле $field должно быть валидной почтой!");
        }
    }
}