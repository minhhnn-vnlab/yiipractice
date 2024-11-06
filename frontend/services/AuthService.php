<?php

namespace frontend\services;

use common\models\LoginForm;
use common\models\RegisterForm;

use Yii;


class AuthService
{
    public function handleLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return Yii::$app->controller->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post())) {
            return $this->processLogin($model);
        }

        $model->password = '';
        return $model;
    }

    private function processLogin(LoginForm $model)
    {
        $httpClient = Yii::$app->httpClient;
        $response = $httpClient
            ->post('auth/login', $model->toArray())
            ->setHeaders($this->getRequestHeaders())
            ->send();

        if ($response->statusCode == 200) {
            return $this->redirectToVerification($response, $model);
        } else {
            Yii::$app->session->setFlash("error", $response->data["message"] ?? 'Login failed.');
        }

        return $model;
    }
    private function redirectToVerification($response, LoginForm $model)
    {
        $id = $response->data["data"]["verification"]["id"];
        $method = $response->data["data"]["verification"]["verification_method"];
        return Yii::$app->controller->redirect("/site/verify-login?id=$id&method=$method&email={$model->email}");
    }

}
