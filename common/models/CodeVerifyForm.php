<?php
namespace common\models;

use yii\base\Model;

class CodeVerifyForm extends Model {
    public $code;

    public function rules() {
        return [
            ["code", "required"],
            ["code", "string", "max" => 6],
        ];
    }
}