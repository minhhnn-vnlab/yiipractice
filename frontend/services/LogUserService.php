<?php

namespace frontend\services;

use common\models\CodeVerifyForm;
use Yii;
use common\models\User;

class LogUserService
{
    public function updateCodeUnlock($user)
    {
        $client = Yii::$app->httpClient;
        $response = $client->post("user/updateCodeUnlock")
            ->setData(['user' => $user])
            ->send();

        if ($response->data['status'] == 200) {
            return true;
        } else {
            return false;
        }
    }
    public function verifyCodeUnlock(CodeVerifyForm $model, $id)
    {
        $user = User::findOne($id);
        if ($this->updateCodeUnlock($user)) {
            return ['redirect' => '/site/login'];
        } else {
            return ['redirect' => '/site/signup'];
        }
        if ($model->validate()) {
            $model->load(Yii::$app->request->post());
            $client = Yii::$app->httpClient;
            $response = $client->post("user/verifyCodeUnlock", $model->toArray())->send();

            if ($response->data['status'] == 200) {
                return ['redirect' => '/site/verify-new-password'];
            } else {
                return ['redirect' => '/site/unlock?id=' . $id];
            }
        }
        return false;
    }
}
