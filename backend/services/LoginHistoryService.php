<?php

namespace backend\services;

use \common\models\LoginHistory;
use Yii;
use yii\base\Request;

class LoginHistoryService
{
    public function createSuccess($user, Request $request)
    {
        return $this->createWithMessage($user, "login_success", $request);
    }

    public function createWithMessage($user, $message, Request $request)
    {
        $login_history = new LoginHistory();
        $login_history->user_id = $user->id;
        $login_history->message = $message;
        $remoteIp = Yii::$app->request->headers->get('X-Real-IP');
        $login_history->ip_address = $remoteIp;
        $login_history->user_agent = Yii::$app->request->userAgent;
        $login_history->login_time = Yii::$app->formatter->asDatetime(time(), 'yyyy-MM-dd HH:mm:ss');
        if (!$login_history->save()) {
            return false;
        }
        return true;
    }

    public function getRecentLoginHistories($user)
    {
        $query = LoginHistory::find()
            ->where(['user_id' => $user->id])
            ->orderBy(['login_time' => SORT_DESC]);
        $login_histories = $query->all();

        return $login_histories;
    }

    public function getRecentFailLoginHistories($user, $message)
    {
        $login_histories = $this->getRecentLoginHistories($user);

        $fail_count = 0;
        $max_fail_count = 5;
        foreach ($login_histories as $login_history) {
            if ($login_history->message == $message) {
                $fail_count++;
            } else if ($login_history->message == 'login_success') {
                if ($fail_count >= $max_fail_count) {
                    return true;
                } else {
                    return $fail_count;
                }
            }
        }

        if ($fail_count >= $max_fail_count) {
            return true;
        } else {
            return $fail_count;
        }
    }
}
