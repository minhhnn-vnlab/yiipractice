<?php
namespace frontend\services;

use frontend\repositories\LoginHistoryRepository;
use Yii;
use yii\base\Component;

class LoginHistoryService extends Component
{
    protected $loginHistoryRepository;

    public function __construct(LoginHistoryRepository $loginHistoryRepository, $config = [])
    {
        $this->loginHistoryRepository = $loginHistoryRepository;
        parent::__construct($config);
    }

    public function getLoginHistories($userId)
    {
        return $this->loginHistoryRepository->getLoginHistories($userId);
    }
}