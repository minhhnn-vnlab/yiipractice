<?php
namespace frontend\repositories;

use common\models\LoginHistory;
use Yii;
use yii\data\ArrayDataProvider;

class LoginHistoryRepository
{
    public function getLoginHistories($userId)
    {
        $client = Yii::$app->httpClient;
        $response = $client->get("login-histories?filter[user_id]=$userId&sort=-login_time")->send();

        if ($response->statusCode == 200) {
            $loginHistories = array_map(function ($data) {
                $model = new LoginHistory();
                $model->setAttributes($data);
                return $model;
            }, $response->data);

            return new ArrayDataProvider([
                'allModels' => $loginHistories,
                'pagination' => [
                    'pageSize' => 10, // Số lượng bản ghi trên mỗi trang
                ],
            ]);
        }
        return new ArrayDataProvider([
            'allModels' => [],
        ]);
    }
}