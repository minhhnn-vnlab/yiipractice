<?php
namespace backend\controllers;

use yii\rest\ActiveController;
use Yii;
use yii\web\Response;
use backend\models\User;
use backend\services\UserService;
use common\models\Setup2FAForm;
use yii\filters\ContentNegotiator;

class UserController extends ActiveController
{
    public $modelClass = "backend\models\User";
    protected $userService;

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['contentNegotiator'] = [
            'class' => ContentNegotiator::class,
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
            ],
        ];
        return $behaviors;
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
    
    public function actionUpdateTwoFa()
    {
        $model = new Setup2FAForm();
        $model->attributes = Yii::$app->request->bodyParams;

        if (!$model->validate()) {
            return $this->handleResponse(400, 'Bad request', $model->getFirstErrors());
        }

        $updateResult = $this->userService->updateTwoFa($model);
        if (isset($updateResult['error'])) {
            return $this->handleResponse($updateResult['status'], $updateResult['error']);
        }

        return [
            'message' => $updateResult['message'],
            'data' => $updateResult['data'],
        ];
    }

    public function __construct($id, $module, UserService $userService, $config = [])
    {
        $this->userService = $userService;
        parent::__construct($id, $module, $config);
    }

    protected function handleResponse($statusCode, $message, $errors = null)
    {
        Yii::$app->response->statusCode = $statusCode;
        return [
            'error' => $message,
            'message' => $errors ?? null,
        ];
    }

    // public function actionIndex() {
    //     $users = Yii::$app->db->createCommand("SELECT * FROM users")->queryAll();
    //     return $users;
    // }
}