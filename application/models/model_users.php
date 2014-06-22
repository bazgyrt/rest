<?php

use core\Model;

class Model_Users extends Model {

    protected $login;
    protected $password;
    protected $firstname;
    protected $lastname;
    protected $phone;
    protected $token;

    function __construct()
    {
        parent::__construct();
    }

    public function rules(){
        return array(
            array(
                'class' => "core\\validators\\BaseValidator",
                'params' => array(
                    'registration' => array(
                        'login' => array('required' => true, 'email' => true, 'min' => 3),
                        'password' => array('required' => true, 'min' => 6),
                        'confirm_password' => array('required' => true, 'equal' => 'password'),
                        'firstname' => array('required' => true),
                        'phone' => array('pattern' => '/\+?\d\s?\-?\(?\d{3,}\)?(\s?\-?\d{2,3})?\s?\-?\d{2}(\s?\-?\d{2})?/')
                    )
                )
            )
        );
    }

    public function registration()
    {
        if (!empty($this->password)) {
            $this->password = md5($this->password);
            return $this->insert();
        }
    }

    public function login()
    {
        $sql = "select * from $this->table where login='" . $this->login . "' and password='" . md5($this->password) . "'";
        $user = $this->db->query($sql);
        $user = $user->fetch(PDO::FETCH_ASSOC);

        if (!empty($user)) {
            $this->token = md5(rand());
            $this->update(array('token' => $this->token), array('id' => $user['id']));
            return array('token' => $this->token, 'id' => $user['id']);
        } else {
            $this->errors[] = "Пользователь не найден!";
            return false;
        }

    }

}