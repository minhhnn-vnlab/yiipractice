<?php
namespace backend\services;

use backend\models\User;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\SvgWriter;
use common\models\Setup2FAForm;
use backend\models\Twofaverification;
use \backend\utils\DateConvert;
use Yii;

class UserService
{
    public function getByEmail($email) {
        $user = User::find()->where(['email' => $email])->one();
        return $user;
    }
    public function getTwoFaEnabledById($id) {
        return User::find()
            ->select('two_fa_enabled')
            ->where(['id' => $id])
            ->scalar();
    }
    public function getTwofaMethodById($id) {
        if($id){
            return User::find()
            ->select('two_fa_method')
            ->where(['id'=> $id])
            ->scalar();
        }
    }
    public function updateTwoFa(Setup2FAForm $model)
    {
        $user = User::findOne($model->user_id);
        if (!$user) {
            return ['error' => 'User not found', 'status' => 404];
        }

        if ($model->two_fa_method == 'authenticator') {
            $tfa = Yii::$app->tfa;
            $result = $tfa->verifyCode($user->two_fa_secret, $model->code);

            if (!$result) {
                return ['error' => 'Invalid authenticator code', 'status' => 400];
            }
            $user->two_fa_enabled=true;
        }else if ($model->two_fa_method == 'email') {
            $login_verification = Twofaverification::find()->where(['user_id' => $model->user_id])->one() ?? new Twofaverification();
            $result = $login_verification->code === $model->code;
            if (!$result) {
                return ['error' => 'Invalid email code', 'status' => 400];
            }
            $user->two_fa_enabled=true;
        }else{
            $user->two_fa_enabled=false;
        }

        $user->two_fa_method = $model->two_fa_method ?: null;
        
        if (!$user->save()) {
            return ['error' => 'Failed to save user', 'status' => 500];
        }

        return ['message' => 'Successfully updated user\'s 2FA method', 'data' => $user];
    }
    public function generateTwoFactorQr(User $user)
    {
        $writer = new SvgWriter();
        $email = $user->email;
        $secret = $user->two_fa_secret;

        $qrCode = new QrCode(
            data: "otpauth://totp/VNLabTraining:$email?secret=$secret",
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Low,
            size: 300,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            foregroundColor: new Color(0, 0, 0),
            backgroundColor: new Color(255, 255, 255)
        );

        return $writer->write($qrCode)->getString();
    }

    public function sendCodeEmail($login_verification, $user)
    {
            $time = time();
            $exp = $time + 60;

            $login_verification->setAttributes([
                'user_id' => $user->id,
                'issued_at' => DateConvert::convertToSQL($time),
                'expired_at' => DateConvert::convertToSQL($exp),
                'code' => Yii::$app->security->generateRandomString(6),
                'num_try' => 0,
            ]);

            if (!$login_verification->save()) {
                return false;
            }

        $result = Yii::$app->mailer->compose()
            ->setTo($login_verification->user->email)
            ->setFrom(['a@example.com' => 'Your App'])
            ->setSubject('Verification Code')
            ->setTextBody("Your verification code is: $login_verification->code")
            ->send();

        return $result;
    }
}