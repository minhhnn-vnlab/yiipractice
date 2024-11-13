<?php

namespace frontend\services;

use common\models\CodeVerifyForm;
use Yii;
use common\models\User;
use yii\httpclient\Client;

class LogUserService
{
    public function updateCodeUnlock($user)
    {
        /**
         * @var Client
         */
        $client = Yii::$app->httpClient;
        $response = $client->post("user/update-code-unlock", ['id' => $user->id])
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
        Yii::debug($user->locked );
        $this->updateCodeUnlock($user);
        $model->load(Yii::$app->request->post());
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $client = Yii::$app->httpClient;
            $response = $client->post("user/verify-code-unlock", $model->toArray())->send();
            Yii::debug($response);
            if ($response->data['status'] == 200) {
                return ['redirect' => '/site/verify-new-password?id=' . $id];
            } else {
                Yii::$app->session->setFlash("error", "Incorrect code entered");
                return ['redirect' => '/site/unlock?id=' . $id];
            }
        }
    
        return false;
    }
}
