<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "users".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $email
 * @property string|null $password_hash
 * @property bool|null $two_fa_enabled
 * @property string|null $two_fa_method
 * @property string|null $two_fa_secret
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property LoginHistory[] $loginHistories
 * @property TwoFaTokens[] $twoFaTokens
 */
class Users extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'users';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['two_fa_enabled'], 'boolean'],
            [['two_fa_method'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 32],
            [['email'], 'string', 'max' => 256],
            [['password_hash'], 'string', 'max' => 60],
            [['two_fa_secret'], 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'email' => 'Email',
            'password_hash' => 'Password Hash',
            'two_fa_enabled' => 'Two Fa Enabled',
            'two_fa_method' => 'Two Fa Method',
            'two_fa_secret' => 'Two Fa Secret',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[LoginHistories]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLoginHistories()
    {
        return $this->hasMany(LoginHistory::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[TwoFaTokens]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTwoFaTokens()
    {
        return $this->hasMany(TwoFaTokens::class, ['user_id' => 'id']);
    }
}
