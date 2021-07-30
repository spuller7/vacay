<?php

namespace app\models;
use app\core\Application;
use app\core\Model;

class LoginForm extends Model
{

    public string $username = '';
    public string $password = '';

    public function rules(): array
    {
        return [
            'username' => [self::RULE_REQUIRED],
            'password' => [self::RULE_REQUIRED],
        ];
    }

    public function labels(): array
    {
        return [
            'username' => 'Username',
            'password' => 'Password',
        ];
    }

    public function login()
    {
        $user = new User();
        $user = $user->findByUsername($this->username);

        if (!$user)
        {
            $this->addError('username', 'Username not found');
            return false;
        }

        if (!$user->authenticate($this->password))
        {
            $this->addError('password', 'Password is incorrect');
            return false;
        }

        return Application::$app->login($user);
    }
}