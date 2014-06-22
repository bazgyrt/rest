<?php

use core\Controller;


class Controller_users extends Controller
{
    function behaviors() {
        return array(
            array(
                'class' => "core\\filters\\RequestFilter",
                'params' => array(
                    'user' => array(
                        'GET' => 'user',
                        'POST' => 'create',
                        'DELETE' => 'delete',
                        'PUT' => 'update'
                    )
                )
            )
        );
    }

    function action_index()
    {
        $dir = dirname(__FILE__);
        $config = require($dir . "/../core/config/mail.php");
        print_r($config);
    }

    function action_new()
    {
        $model = new Model_Users();

        $props = $model->getProps();
        $password_key = array_search('password', $props) + 1;
        array_splice($props, $password_key, 0, 'confirm_password');

        $this->success('registration', $props);
    }

    function action_login()
    {
        $model = new Model_Users();

        if ($model->load()) {
            if ($data = $model->login()) {
                $this->success($data);
                return;
            }
        }
        $this->fail($model->errors);
    }

    function user($id, $token = "no")
    {
        $model = new Model_Users();

        $user = $model->selectOne(array('id' => $id, 'token' => $token), array('login', 'firstname', 'lastname', 'phone'));
        $this->success($user);
    }

    function create()
    {
        $model = new Model_Users();

        if ($model->load() && $model->validate('registration') && $model->registration()) {
            $mail = new \PHPMailer();

            $dir = dirname(__FILE__);
            $config = require($dir . "/../core/config/mail.php");
            $mail->isSMTP();
            $mail->Host = $config['host'];
            $mail->Port = $config['port'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['username'];
            $mail->Password = $config['password'];
            $mail->SMTPSecure = $config['secure'];

            $mail->isHTML(true);
            $mail->CharSet = "utf-8";
            $mail->From = 'from@example.com';
            $mail->FromName = 'Test';
            $mail->addAddress($model->getProperty('login'));     // Add a recipient
            $mail->Subject = 'Вы успешно зарегистрировались!';
            $mail->Body    = '<b>Спасибо за регистрацию!</b>';
            $mail->send();

            $this->success("Пользователь добавлен");
        } else {
            $this->fail($model->errors);
        }

    }
    function delete()
    {
        echo "delete users";
    }
    function update()
    {
        echo "update users";
    }
}