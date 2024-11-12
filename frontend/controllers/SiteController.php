<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use common\models\SignupForm;
use common\models\CodeVerifyForm;
use frontend\services\AuthService;
use frontend\services\Setup2faService;
/**
 * Site controller
 */
class SiteController extends Controller
{
    protected $authService;
    protected $setup2faService;
    public function __construct($id, $module, AuthService $authService, Setup2faService $setup2faService, $config = [])
    {
        $this->authService = $authService;
        $this->setup2faService = $setup2faService;
        parent::__construct($id, $module, $config);
    }
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => \yii\web\ErrorAction::class,
            ],
            'captcha' => [
                'class' => \yii\captcha\CaptchaAction::class,
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Logs in a user.
     *
     * @return mixed
     */
    public function actionLogin()
    {
        $model = $this->authService->handleLogin();
        if ($model instanceof LoginForm) {
            return $this->render('login', ['model' => $model]);
        }
        return $model;
    }
    public function actionLoginHistory()
    {
        $userId = Yii::$app->user->id;
        $dataProvider = Yii::$app->loginHistoryService->getLoginHistories($userId);
        Yii::debug('Total count: ' . $dataProvider->getTotalCount());
        return $this->render("loginhistory", [
            'dataProvider' => $dataProvider
        ]);
    }
    public function actionUpdateTwofa()
    {
        $user = Yii::$app->user->identity;
        $twoFamethod = $this->setup2faService->getTwoFA($user);
        return $this->render('setup2fa', [
            'user' => $user,
            'method' => $twoFamethod
        ]);
    }
    
    public function actionVerifyLogin()
    {
        $model = new CodeVerifyForm();
        $id = Yii::$app->session->get('verification_id');
        $method = Yii::$app->session->get('verification_method');
        $email = Yii::$app->session->get('verification_email');
        $result = $this->authService->handleVerifyLogin($model, $id, $method, $email);

        if (isset($result['redirect'])) {
            return $this->redirect($result['redirect']);
        }


        return $this->render('loginVerification', [
            'method' => $method,
            'email' => $email,
            'model' => $model,
        ]);
    }
    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }



    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {
        $model = $this->authService->handleSignup();
        if ($model  instanceof SignupForm) {
            return $this->render('signup', [
                'model' => $model,
            ]);
        }
        return $model;
    }

    
}
