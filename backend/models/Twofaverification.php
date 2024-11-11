<?php

namespace backend\models;
use backend\models\User;
use Yii;

/**
 * This is the model class for table "twofaverification".
 *
 * @property int $id
 * @property int|null $user_id
 * @property string|null $code
 * @property int|null $active
 * @property int|null $max_try
 * @property int|null $num_try
 * @property string|null $issued_at
 * @property string|null $expired_at
 *
 * @property User $user
 */
class Twofaverification extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'twofaverification';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'active', 'max_try', 'num_try'], 'default', 'value' => null],
            [['user_id', 'active', 'max_try', 'num_try'], 'integer'],
            [['issued_at', 'expired_at'], 'safe'],
            [['code'], 'string', 'max' => 6],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
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
            'code' => 'Code',
            'active' => 'Active',
            'max_try' => 'Max Try',
            'num_try' => 'Num Try',
            'issued_at' => 'Issued At',
            'expired_at' => 'Expired At',
        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function isExpired()
    {
        return time() > strtotime($this->expired_at) || $this->active == 0;
    }
}
