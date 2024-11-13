<?php

namespace frontend\models;

use Yii;
use yii\base\Model;

class ResetPasswordForm extends Model
{
    public $newPassword;
    public $confirmPassword;

    public function rules()
    {
        return [
            [['newPassword', 'confirmPassword'], 'required'],
            ['newPassword', 'validatePassword'],
            ['confirmPassword', 'compare', 'compareAttribute' => 'newPassword', 'message' => 'Mật khẩu nhập lại không đúng'],
        ];
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
    public function attributeLabels()
    {
        return [
            'newPassword' => 'Mật khẩu mới',
            'confirmPassword' => 'Nhập lại mật khẩu',
        ];
    }
}
