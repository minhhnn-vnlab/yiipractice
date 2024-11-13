<?php

namespace backend\services;

use \common\models\User;
use \backend\models\Twofaverification;
use \backend\utils\DateConvert;
use common\models\Setup2FAForm;
use Yii;

class TwofaverificationService
{
    public function getById($id)
    {
        return Twofaverification::findOne($id);
    }
    public function createFromUser(User $user)
    {
        $login_verification = Twofaverification::find()->where(['user_id' => $user->id])->one() ?? new Twofaverification();
        $time = time();
        $exp = $time + 60;

        $login_verification->setAttributes([
            'user_id' => $user->id,
            'issued_at' => DateConvert::convertToSQL($time),
            'expired_at' => DateConvert::convertToSQL($exp),
            'code' => $user->two_fa_method == 'email' ? "vnlab123" : null,
            'active' => 1,
            'num_try' => 0,
        ]);

        if (!$login_verification->save()) {
            return false;
        }

        return $login_verification;
    }

    public function deactivate(Twofaverification $login_verification)
    {
        $login_verification->active = 0;
        return $login_verification->save();
    }

    public function verificateAuthenticator($user, $code)
    {
        $secret = $user->two_fa_secret;

        $tfa = Yii::$app->tfa;
        return $tfa->verifyCode($secret, $code);
    }
}
