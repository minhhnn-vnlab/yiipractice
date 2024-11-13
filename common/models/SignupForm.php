<?php

namespace common\models;

use Yii;
use yii\base\Model;
use common\models\User;
use yii\validators\EmailValidator;

/**
 * Signup form
 */
class SignupForm extends Model
{
    public $username;
    public $email;
    public $password;

    private $_user;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'email', 'password'], 'required'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
            ['email', 'validateEmail'],
        ];
    }

    /**
     * Signs user up.
     *
     * @return bool whether the creating new account was successful and email was sent
     */
    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }

        $user = $this->getUser();

        return $user->save();
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if ($this->hasErrors()) {
            // Check if password is less than 8 characters
            if (strlen($this->$attribute) < 8) {
                $this->addError($attribute, 'Password must be at least 8 characters long.');
            }
        }
    }

    /**
     * Validates the email.
     * This method serves as the inline validation for email.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validateEmail($attribute, $params)
    {
        if (!$this->hasErrors()) {
            try {
                $user = User::find()->where(['email' => $this->$attribute])->one();
                if (!empty($user)) {
                    $this->addError($attribute, 'User with this email already exists.');
                }
                $emailValidator = new EmailValidator();
                if (!$emailValidator->validate($this->email)) {
                    $this->addError($attribute, 'Email is not valid.');
                }
            } catch (\Exception $e) {
                $this->addError($attribute, $e->getMessage());
            }
        }
    }
    /**
     * Return a new user.
     *
     * @return User
     */
    public function getUser()
    {
        $user = new User();
        $user->name = $this->username;
        $user->email = $this->email;
        $user->password_hash = Yii::$app->security->generatePasswordHash($this->password);
        $user->two_fa_secret = Yii::$app->tfa->createSecret();
        $user->two_fa_enabled = false;

        return $user;
    }
}
