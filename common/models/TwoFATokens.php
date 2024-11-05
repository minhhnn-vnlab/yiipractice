<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "two_fa_tokens".
 *
 * @property int $id
 * @property int|null $user_id
 * @property string|null $token
 * @property string|null $created_at
 * @property string|null $expires_at
 *
 * @property Users $user
 */
class TwoFATokens extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'two_fa_tokens';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id'], 'default', 'value' => null],
            [['user_id'], 'integer'],
            [['created_at', 'expires_at'], 'safe'],
            [['token'], 'string', 'max' => 8],
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
            'token' => 'Token',
            'created_at' => 'Created At',
            'expires_at' => 'Expires At',
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
