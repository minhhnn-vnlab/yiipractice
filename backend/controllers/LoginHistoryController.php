<?php

namespace backend\controllers;

use yii\data\ActiveDataProvider;
use yii\rest\ActiveController;
use yii\web\Response;
use yii\filters\ContentNegotiator;
use common\models\LoginHistory;
use backend\components\CustomDataProvider;
use yii;

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


    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        return $actions;
    }

    public function actionIndex()
    {
        $requestParams = Yii::$app->getRequest()->getBodyParams();
        if (empty($requestParams)) {
            $requestParams = Yii::$app->getRequest()->getQueryParams();
        }

        $query = LoginHistory::find();

        if (isset($requestParams['filter']['user_id'])) {
            $query->andWhere(['user_id' => $requestParams['filter']['user_id']]);
        }

        if (isset($requestParams['sort'])) {
            $sort = $requestParams['sort'];
            if ($sort[0] == '-') {
                $sort = substr($sort, 1);
                $query->orderBy([$sort => SORT_DESC]);
            } else {
                $query->orderBy([$sort => SORT_ASC]);
            }
        }

        return new CustomDataProvider([
            'query' => $query,
        ]);
    }
}
