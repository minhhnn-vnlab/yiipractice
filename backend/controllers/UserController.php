<?php

namespace backend\controllers;

use backend\models\Twofaverification;
use yii\rest\ActiveController;
use Yii;
use yii\web\Response;
use common\models\User;
use backend\services\UserService;
use common\models\Setup2FAForm;
use yii\filters\ContentNegotiator;
use backend\services\TwofaverificationService;
use common\models\CodeVerifyForm;

class UserController extends ActiveController
{
    public $modelClass = "common\models\User";
    protected $userService;

    protected TwofaverificationService $twofaverificationService;
    public function behaviors()
    {
        return [
            'corsFilter' => [
                'class' => \yii\filters\Cors::class,
                'cors' => [
                    'Origin' => ['http://y2aa.test:8081'],
                    'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                    'Access-Control-Request-Headers' => ['*'],
                    'Access-Control-Allow-Credentials' => true,
                    'Access-Control-Max-Age' => 86400,
                ],
            ],
        ];
    }
    public function __construct(
        $id,
        $module,
        TwofaverificationService $twofaverificationService,
        UserService $userService,
        $config = []
    ) {
        $this->twofaverificationService = $twofaverificationService;
        $this->userService = $userService;
        parent::__construct($id, $module, $config);
    }

    public function actionGetQrCode($id)
    {
        $id = Yii::$app->request->get("id");
        $user = User::findOne($id);
        if (!$user) {
            return $this->handleResponse(404, 'User not found');
        }

        $qrCodeBase64 = $this->userService->generateTwoFactorQr($user);

        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'qrCode' => $qrCodeBase64
        ];
    }

    public function actionSendCodeEmail($id)
    {
        $id = Yii::$app->request->get("id");
        $user = User::findOne($id);
        if (!$user) {
            return $this->handleResponse(404, 'User not found');
        }

        $login_verification = Twofaverification::find()->where(['user_id' => $user->id])->one() ?? new Twofaverification();
        if ($this->userService->sendCodeEmail($login_verification, $user)) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'status' => 200,
                'message' => 'Success send code email',
            ];
        }
        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'status' => 500,
            'message' => 'Fail send code email',
        ];
    }


    public function actionUpdateTwoFa()
    {
        $model = new Setup2FAForm();
        $model->attributes = Yii::$app->request->bodyParams;

        if (!$model->validate()) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'status' => 400,
                'error' => 'Bad request',
                'errors' => $model->getFirstErrors()
            ];
        }

        $updateResult = $this->userService->updateTwoFa($model);
        if (isset($updateResult['error'])) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'status' => $updateResult['status'],
                'error' => $updateResult['error']
            ];
        }

        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'message' => $updateResult['message'],
            'data' => $updateResult['data'],
        ];
    }

    public function actionGetTwofaMethod()
    {
        $id = Yii::$app->request->get('id');
        $twofamethod = $this->userService->getTwofaMethodById($id);
        Yii::$app->response->format = Response::FORMAT_JSON;
        if ($twofamethod) {
            return [
                'status' => 200,
                'TwoFaMethod' => $twofamethod,
                'message' => 'success'
            ];
        } else {
            return [
                'status' => 400,
                'TwoFaMethod' => $twofamethod,
                'message' => 'Not load method'
            ];
        }
    }
    public function actionVerifyCodeUnlock()
    {
        $model = new CodeVerifyForm();
        $model->attributes = Yii::$app->request->post();
        if ($model->validate()) {
            $login_verification = Twofaverification::find()->where(['user_id' => $model->user_id])->one() ?? new Twofaverification();
            if ($login_verification->code === $model->code) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'status' => 200,
                    'message' => 'Success verify code'
                ];
            }
        }
        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'status' => 400,
            'message' => 'Fail verify code'
        ];
    }
    public function actionUpdateCodeUnlock()
    {
        $user = Yii::$app->request->post('user');
        if ($user) {
            $login_verification = Twofaverification::find()->where(['user_id' => $user['id']])->one() ?? new Twofaverification();
            if ($this->userService->sendCodeEmail($login_verification, $user)) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'status' => 200,
                    'message' => 'Success update code'
                ];
            }
        }
        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'status' => 400,
            'message' => 'Fail update code'
        ];
    }
    protected function handleResponse($statusCode, $message, $errors = null)
    {
        Yii::$app->response->statusCode = $statusCode;
        return [
            'error' => $message,
            'message' => $errors ?? null,
        ];
    }
}
