<?php
namespace backend\controllers;

use yii\rest\ActiveController;
use yii\web\Response;
use yii\filters\ContentNegotiator;
class UsersController extends ActiveController
{
    public $modelClass = 'common\models\Users';
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

    public function actionCreate()
    {
        $model = new Users();

        if ($this->request->isPost) {
            $data = $this->request->post();
            $model->load($data, '');

            if ($model->validate()) {
                if ($model->save()) {
                    Yii::warning('Validate success! ', __METHOD__);
                    return ['success' => true, 'data' => $model];
                } else {
                    return ['success' => false, 'errors' => $model->getErrors()];
                }
            } else {
                return ['success' => false, 'errors' => $model->getErrors()];
            }
        }

        return ['success' => false, 'errors' => ['No data received']];
    }
}