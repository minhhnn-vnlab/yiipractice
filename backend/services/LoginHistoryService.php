<?php
namespace backend\services;

use \common\models\LoginHistory;
use Yii;
use yii\base\Request;

class LoginHistoryService {
    public function createSuccess($user, Request $request) {
        return $this->createWithMessage($user, "login_success", $request);
    }

    public function createWithMessage($user, $message, Request $request) {
        $login_history = new LoginHistory();
        $login_history->user_id = $user->id;
        $login_history->message = $message;
        $remoteIp = Yii::$app->request->headers->get('X-Real-IP');
        $login_history->ip_address = $remoteIp;
        $login_history->user_agent = Yii::$app->request->userAgent;
        $login_history->login_time = Yii::$app->formatter->asDatetime(time(), 'yyyy-MM-dd HH:mm:ss');
        if(!$login_history->save()) {
            return false;
        }
        return true;
    }

    /**
     * Get recent login historiesD
     * @param \backend\models\User $user
     * @param int $limit
     * @return LoginHistory[]
     */
    public function getRecentLoginHistories($user, $limit = 5) {
        $query = LoginHistory::find()
            ->where(['user_id' => $user->id])
            ->orderBy(['login_time'=> SORT_DESC])
            ->limit( $limit );
        $login_histories = $query->all();

        return $login_histories;
    }

    public function getRecentFailLoginHistories($user, $limit = 5) {
        $login_histories = $this->getRecentLoginHistories($user, $limit);
    
        $failed_login_histories = array_filter($login_histories, function($login_history) {
            return $login_history->isFailed();
        });
    
    
        return $failed_login_histories;
    }
}