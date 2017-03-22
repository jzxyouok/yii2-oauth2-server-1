<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */
namespace yuncms\oauth2\models;

use Yii;
use yii\db\ActiveRecord;
use League\OAuth2\Server\Entities\ClientEntityInterface;

/**
 * Class Client
 * @property int $client_id 客户端ID
 * @property int $user_id 所属用户ID
 * @property string $name 客户端名称
 * @property string $secret 客户端密钥
 * @property string $redirect_uri 回调域
 * @property string $token_type
 * @property string $grant_type
 * @property int $state 状态
 * @property string $created_at
 * @property string $updated_at
 *
 * @package yuncms\oauth2\models
 */
class Client extends ActiveRecord  implements ClientEntityInterface
{
    //状态
    const STATE_DISABLED = 0;
    const STATE_ACTIVE = 1;

    const GRANT_TYPE_AUTHORIZATION_CODE = 1;
    const GRANT_TYPE_IMPLICIT = 2;
    const GRANT_TYPE_PASSWORD = 3;
    const GRANT_TYPE_CLIENT_CREDENTIALS = 4;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior'
            ],
            'blameable' => [
                'class' => 'yii\behaviors\BlameableBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'user_id',
                ],
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%oauth2_client}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'redirect_uri'], 'required'],
            [['created_at', 'updated_at'], 'integer'],
            [['client_secret', 'grant_type'], 'string', 'max' => 80],
            [['redirect_uri'], 'string', 'max' => 2000],
            ['state', 'default', 'value' => self::STATE_ACTIVE],
            ['state', 'in', 'range' => [self::STATE_ACTIVE, self::STATE_DISABLED]],
        ];
    }

    /**
     * 定义用户关系
     * @return \yii\db\ActiveQueryInterface
     */
    public function getUser()
    {
        return $this->hasOne(Yii::$app->user->identityClass, ['id' => 'user_id']);
    }

    /**
     * 保存前自动生成secret
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->setAttribute('client_secret', Yii::$app->security->generateRandomString());
            }
            return true;
        } else {
            return false;
        }
    }

    public static function grants()
    {
        return [
            static::GRANT_TYPE_AUTHORIZATION_CODE => 'authorization_code',
            static::GRANT_TYPE_IMPLICIT => 'implicit',
            static::GRANT_TYPE_PASSWORD => 'password',
            static::GRANT_TYPE_CLIENT_CREDENTIALS => 'client_credentials',
        ];
    }

    /**
     * 是否是作者
     * @return bool
     */
    public function isAuthor()
    {
        return $this->user_id == Yii::$app->user->id;
    }

    /**
     * Get the client's identifier.
     * @return string
     */
    public function getIdentifier()
    {
        return $this->client_id;
    }

    /**
     * Get the client's name.
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the registered redirect URI (as a string).
     *
     * Alternatively return an indexed array of redirect URIs.
     * @return string
     */
    public function getRedirectUri()
    {
        return $this->redirect_uri;
    }
}