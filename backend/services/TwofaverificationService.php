<?php
namespace backend\services;

use \backend\models\User;
use \backend\models\Twofaverification;
use \backend\utils\DateConvert;
use Yii;

class TwofaverificationService {
    public function getById($id) {
        return Twofaverification::findOne($id);
    }
    public function createFromUser(User $user) {
        $login_verification = Twofaverification::find()->where(['user_id' => $user->id])->one() ?? new Twofaverification();
        $time = time();
        $exp = $time + 60;

        $login_verification->setAttributes([
            'user_id' => $user->id,
            'issued_at' => DateConvert::convertToSQL($time),
            'expired_at' => DateConvert::convertToSQL($exp),
            'code' => $user->two_fa_method == 'email' ? Yii::$app->security->generateRandomString(6) : null,
            'active' => 1,
            'num_try' => 0,
        ]);

        if(!$login_verification->save()) {
            return false;
        }

        return $login_verification;
    }

    public function deactivate(Twofaverification $login_verification) {
        $login_verification->active = 0;
        return $login_verification->save();
    }
}