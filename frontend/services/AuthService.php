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
            if($response->data["data"]["two_fa_enabled"] === "true") {
                return $this->redirectToVerification($response, $model);
            }else{
                Yii::$app->user->login($response->data["data"]["user"]); // Yii sẽ lưu thông tin người dùng vào phiên làm việc (session) và đánh dấu người dùng là đã đăng nhập.
                return Yii::$app->controller->goHome();
            }
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
    public function handleVerifyLogin(TwoFAForm $model)
    {
        $id = Yii::$app->request->get("id");
        $method = Yii::$app->request->get("method");
        $email = Yii::$app->request->get("email");

        if (empty($method) || $model->load(Yii::$app->request->post())) {
            return $this->processVerification($model, $id);
        }

        return ['method' => $method, 'email' => $email, 'redirect' => null];
    }
    private function processVerification(TwoFAForm $model, $id)
    {
        $httpClient = Yii::$app->httpClient;
        $response = $httpClient
            ->post("auth/verify?id=$id", $model->toArray())
            ->setHeaders($this->getRequestHeaders())
            ->send();

        if ($response->getStatusCode() == 200) {
            return $this->loginUser($response);
        } else {
            $this->handleVerificationError($response);
            return ['redirect' => $response->data['data']['redirect'] ?? null];
        }
    }
    private function getRequestHeaders()
    {
        return [
            "X-Forwarded-For" => Yii::$app->request->userIP,
            "User-Agent" => Yii::$app->request->userAgent,
        ];
    }
}
