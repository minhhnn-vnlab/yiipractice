<?php

namespace backend\controllers;

use backend\services\LoginHistoryService;
use backend\services\TwofaverificationService;
use backend\services\UserService;
use common\models\LoginForm;
use common\models\SignupForm;
use yii\rest\Controller;
use yii\web\Response;
use Yii;

class AuthController extends Controller
{
    protected TwofaverificationService $twofaverificationService;
    protected UserService $userService;
    protected LoginHistoryService $loginHistoryService;
    public function __construct(
        $id,
        $module,
        TwofaverificationService $twofaverificationService,
        UserService $userService,
        LoginHistoryService $loginHistoryService,
        $config = []
    ) {
        $this->twofaverificationService = $twofaverificationService;
        $this->userService = $userService;
        $this->loginHistoryService = $loginHistoryService;
        parent::__construct($id, $module, $config);
    }
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'contentNegotiator' => [
                'class' => \yii\filters\ContentNegotiator::class,
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
        ]);
    }

    // ACTIONS
    public function actionRegister()
    {
        $model = new SignupForm();
        $model->attributes = Yii::$app->request->post();

        if (!$model->validate()) {
            $errors = $model->getFirstErrors();
            $errorMessages = [];
            foreach ($errors as $attribute => $error) {
                $errorMessages[] = $error;
            }
            return $this->respondWithError(400, $errorMessages);
        }

        $user = $model->getUser();

        try {
            if ($user->save()) {
                return $this->respondWithSuccess(200, 'Register successfully', "false", [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ]
                ]);
            }
            return $this->respondWithError(500, 'Register unsuccessfully', $user);
        } catch (\Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            return $this->respondWithError(500, 'An error occurred.', $e->getMessage());
        }
    }

    public function actionLogin()
    {
        $model = new LoginForm();
        $model->attributes = Yii::$app->request->post();
        $user = $model->login();

        if ($user) {
            return $this->handleSuccessfulLogin($user);
        }

        return $this->handleFailedLogin($model->email);
    }

    public function actionVerify()
    {
        $id = Yii::$app->request->get('id');
        $login_verification = $this->twofaverificationService->getById($id);

        if (!$login_verification) {
            return $this->respondWithError(404, 'Not Found', 'Login Verification with this id is not found');
        }

        if ($login_verification->isExpired()) {
            return $this->handleVerificationExpiration($login_verification);
        }

        return $this->processVerification($login_verification);
    }

    // RESPONSES

    protected function respondWithError($statusCode, $message, $data = null)
    {
        Yii::$app->response->statusCode = $statusCode;
        return [
            'error' => $statusCode === 404 ? 'Not Found' : 'Bad request',
            'message' => $message,
            'data' => $data,
        ];
    }

    protected function respondWithSuccess($statusCode, $message, $two_fa_enabled, $data = [])
    {
        Yii::$app->response->statusCode = $statusCode;
        return [
            'two_fa_enabled' => $two_fa_enabled,
            'message' => $message,
            'data' => $data,
        ];
    }

    // HANDLERS

    protected function handleSuccessfulLogin($user)
    {
        $two_fa_enabled = $this->userService->getTwoFaEnabledById($user->id);
        if ($two_fa_enabled) {
            $login_verification = $this->twofaverificationService->createFromUser($user);

            if (!$login_verification) {
                return $this->respondWithError(500, 'Internal Server Error', 'Something wrong when creating verification');
            }
            if($user->two_fa_method === 'email') {
                $this->userService->sendCodeEmail($login_verification, $user);
            }
            return $this->respondWithSuccess(200, 'Successfully logged in by email and password, continue to verify the login.', "true", [
                'verification' => [
                    'id' => $login_verification->id,
                    'verification_method' => $user->two_fa_method,
                ]
            ]);
        } else {
            $this->loginHistoryService->createSuccess($user, Yii::$app->request);
            return $this->respondWithSuccess(200, 'Successfully logged in by email and password.', "false", [
                "user"=> $user,
            ]);
        }
    }

    protected function handleFailedLogin($email, $reason = 'Email or password is incorrect')
    {

        $user = $this->userService->getByEmail($email);
        if ($user !== null) {
            $this->logFailedLogin($user);
            return $this->respondWithError(403, 'Password is incorrect', 'Unauthorized');
        }

        return $this->respondWithError(403, $reason, 'Unauthorized');
    }

    protected function logFailedLogin($user, $reason = "login_fail_wrong_password")
    {
        $this->loginHistoryService->createWithMessage($user, $reason, Yii::$app->request);
    }

    protected function getClientIp()
    {
        $remoteIp = Yii::$app->request->headers->get('X-Real-IP');
        return $remoteIp ?: Yii::$app->request->userIP;
    }

    protected function handleVerificationExpiration($login_verification)
    {
        $user = $login_verification->user;

        if ($login_verification->active == 1) {
            $this->logVerificationFailure($user, "login_fail_verification_expired", $login_verification->issued_at);
        }

        $this->twofaverificationService->deactivate($login_verification);

        return $this->respondWithError(statusCode: 400, message: "Login Verification already expired", data: ['redirect' => 'login']);
    }


    protected function logVerificationFailure($user, $message, $issuedAt)
    {
        $this->loginHistoryService->createWithMessage($user, $message, Yii::$app->request);
    }

    protected function processVerification($login_verification)
    {
        $code = Yii::$app->request->post('code');
        $user = $login_verification->user;
        if($user->two_fa_method === "authenticator"){
            if($this->twofaverificationService->verificateAuthenticator($user,$code)){
                $this->twofaverificationService->deactivate($login_verification);
                $this->loginHistoryService->createSuccess($login_verification->user, Yii::$app->request);
                return $this->respondWithSuccess(statusCode: 200, message: 'Verified', two_fa_enabled: "true",data: [
                    'user' => [
                        'id' => $login_verification->user->id,
                        'name' => $login_verification->user->name,
                        'email' => $login_verification->user->email,
                        'two_fa_method' => $login_verification->user->two_fa_method,
                        'two_fa_secret' => $login_verification->user->two_fa_secret,
                    ],
                    'redirect' => 'loginhistory'
                ]);
            }else{
                return $this->respondWithError(statusCode: 403, message: 'Verification code is not correct.');
            }
        }else if($user->two_fa_method==="email"){
            if($login_verification->code === $code){
                $this->twofaverificationService->deactivate($login_verification);
                $this->loginHistoryService->createSuccess($login_verification->user, Yii::$app->request);
                return $this->respondWithSuccess(statusCode: 200, message: 'Verified', two_fa_enabled: "true",data: [
                    'user' => [
                        'id' => $login_verification->user->id,
                        'name' => $login_verification->user->name,
                        'email' => $login_verification->user->email,
                        'two_fa_method' => $login_verification->user->two_fa_method,
                        'two_fa_secret' => $login_verification->user->two_fa_secret,
                    ],
                    'redirect' => 'loginhistory'
                ]);
            }else{
                return $this->respondWithError(statusCode: 403, message: 'Verification code is not correct.');
            }
        }
    }
}
