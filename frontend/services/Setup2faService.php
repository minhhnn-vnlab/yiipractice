<?php
namespace frontend\services;

use Yii;
use common\models\Setup2FAForm;

class Setup2faService
{
    public function updateTwoFA(Setup2FAForm $model, $user)
    {
        if ($model->validate() && $model->two_fa_method !== $user->two_fa_method) {
            $client = Yii::$app->httpClient;
            $response = $client->put("user/update-two-fa", $model->toArray())->send();

            if ($response->statusCode == 200) {
                $user->two_fa_method = $model->two_fa_method;
                return true;
            } else {
                Yii::debug($response);
                Yii::$app->session->setFlash("error", $response->data['message']);
            }
        }
        return false;
    }
    public function getQrforAuthenticator($user)
    {
        $client = Yii::$app->httpClient;
        $response = $client->get("user/get-qr?id = $user->id")->send();
        if ($response->statusCode == 200) {
            return $response->data['qrCode'];
        }
        return null;
    }
    public function getTwoFA($user){
        $client = Yii::$app->httpClient;
        $response = $client->get("user/get2FAMethod?id=$user->id")->send();
        if ($response->statusCode == 200) {
            return $response->data["TwoFaMethod"];
        }
        return null;
    }
}