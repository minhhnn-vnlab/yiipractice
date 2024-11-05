<?php
namespace backend\controllers;

use yii\rest\ActiveController;
use yii\web\Response;
use yii\filters\ContentNegotiator;
class LoginHistoryController extends ActiveController
{
    public $modelClass = 'common\models\LoginHistory';
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
}