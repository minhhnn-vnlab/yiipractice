<?php

namespace frontend\services;

use common\models\LoginForm;
use common\models\SignupForm;
use common\models\CodeVerifyForm;
use common\models\User;
use Yii;
use yii\bootstrap5\Html;


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
    public function handleSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post())) {
            return $this->processSignup($model);
        }
        return $model;
    }
    private function processSignup(SignupForm $model)
    {
        $httpClient = Yii::$app->httpClient;
        $response = $httpClient->post("auth/register", $model->toArray())->send();
        Yii::debug($response);
        if ($response->getStatusCode() == 200) {
            Yii::$app->session->setFlash("success", "Thank you for registration. Please login into your account");
            return Yii::$app->controller->redirect("/site/login");
        } else {
            Yii::$app->session->setFlash("error", $response->data["message"]);
        }

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
            if ($response->data["two_fa_enabled"] === "true") {
                return $this->redirectToVerification($response, $model);
            } else {
                $user = new User();
                $user->attributes = $response->data["data"]["user"];
                Yii::$app->user->login($user, 3600 * 24 * 30); // Yii sẽ lưu thông tin người dùng vào phiên làm việc (session) và đánh dấu người dùng là đã đăng nhập.
                return Yii::$app->controller->redirect("/site/login-history");
            }
        } else {
            if ($response->data["message"] === "Account is locked") {
                Yii::$app->session->setFlash("error", "Account is locked");
                $locked = $response->data["data"]["user"]["locked"];
                $id = $response->data["data"]["user"]["id"];
                if ($locked == true) {
                    Yii::$app->session->setFlash('warning', 'Tài khoản của bạn đã bị khóa, truy cập đường link sau để mở khóa: ' . Html::a('Mở khóa tài khoản', ['site/unlock', 'id' => $id]));
                }
            }
            Yii::$app->session->setFlash("error", $response->data["message"] ?? 'Login failed.');
        }

        return $model;
    }
    private function redirectToVerification($response, LoginForm $model)
    {
        $id = $response->data["data"]["verification"]["id"];
        $method = $response->data["data"]["verification"]["verification_method"];
        $email = $model->email;

        Yii::$app->session->set('verification_id', $id);
        Yii::$app->session->set('verification_method', $method);
        Yii::$app->session->set('verification_email', $email);

        return Yii::$app->controller->redirect("/site/verify-login");
    }
    public function handleVerifyLogin(CodeVerifyForm $model, $id, $method, $email)
    {
        if (empty($method) || $model->load(Yii::$app->request->post())) {
            return $this->processVerification($model, $id);
        }

        return ['method' => $method, 'email' => $email, 'redirect' => null];
    }
    private function processVerification(CodeVerifyForm $model, $id)
    {
        $httpClient = Yii::$app->httpClient;
        $response = $httpClient
            ->post("auth/verify?id=$id", $model->toArray())
            ->setHeaders($this->getRequestHeaders())
            ->send();

        if ($response->getStatusCode() == 200) {
            $user = new User();
            $user->attributes = $response->data["data"]["user"];
            Yii::$app->user->login($user, 3600 * 24 * 30);
            return ['redirect' => '/site/login-history'];
        } else {
            $this->handleVerificationError($response);
            return ['redirect' => $response->data['data']['redirect'] ?? null];
        }
    }
    private function handleVerificationError($response)
    {
        if (isset($response->data["message"])) {
            Yii::$app->session->setFlash("error", $response->data["message"]);
            if ($response->data["message"] === "Account is locked") {
                Yii::$app->controller->redirect('/site/login');
            }
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
