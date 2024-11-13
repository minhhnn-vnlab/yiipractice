<?php

namespace common\models;

use Yii;
use common\models\LoginHistory;
use backend\models\Twofaverification;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "users".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $email
 * @property string|null $password_hash
 * @property bool|null $two_fa_enabled
 * @property bool|null $locked
 * @property string|null $two_fa_method
 * @property string|null $two_fa_secret
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property LoginHistory[] $loginHistories
 * @property Twofaverification[] $twofaverifications
 */
class User extends \yii\db\ActiveRecord implements IdentityInterface
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
            [['id'], 'integer'],
            [['two_fa_enabled'], 'boolean'],
            [['two_fa_method'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 32],
            [['email'], 'string', 'max' => 256],
            [['password_hash'], 'string', 'max' => 60],
            [['two_fa_secret'], 'string', 'max' => 64],
            [['locked'], 'boolean'],
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
            'locked' => 'Locked',
        ];
    }


    /**
     * Gets query for [[Twofaverifications]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTwofaverifications()
    {
        return $this->hasMany(Twofaverification::class, ['user_id' => 'id']);
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
        return $this->hasMany(Twofaverification::class, ['user_id' => 'id']);
    }


    /**
     * Finds an identity by the given ID.
     *
     * @param string|int $id the ID to be looked for
     * @return IdentityInterface|null the identity object that matches the given ID.
     */
    public static function findIdentity($id)
    {
        return User::find()->where(['id' => $id])->one();
    }

    /**
     * Finds an identity by the given token.
     *
     * @param mixed $token the token to be looked for
     * @param mixed $type the type of the token
     * @return IdentityInterface|null the identity object that matches the given token.
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    /**
     * @return int|string current user ID
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string current user auth key
     */
    public function getAuthKey()
    {
        return $this->two_fa_secret;
    }
    public function isLocked()
    {
        return $this->locked;
    }
    /**
     * @param string $authKey
     * @return bool if auth key is valid for current user
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }
}
