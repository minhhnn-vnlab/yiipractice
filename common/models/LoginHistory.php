<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "login_history".
 *
 * @property int $id
 * @property int|null $user_id
 * @property string|null $login_time
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string|null $message
 *
 * @property Users $user
 */
class LoginHistory extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'login_history';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id'], 'default', 'value' => null],
            [['user_id'], 'integer'],
            [['login_time'], 'safe'],
            [['ip_address'], 'string'],
            [['user_agent'], 'string', 'max' => 512],
            [['message'], 'string', 'max' => 32],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'login_time' => 'Login Time',
            'ip_address' => 'Ip Address',
            'user_agent' => 'User Agent',
            'message' => 'Message',
        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::class, ['id' => 'user_id']);
    }
}
