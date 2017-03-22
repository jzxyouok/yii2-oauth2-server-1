<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */
namespace yuncms\oauth2\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use chervand\yii2\oauth2\server\components\ResponseTypes\MacTokenResponse;
use chervand\yii2\oauth2\server\components\ResponseTypes\BearerTokenResponse;

/**
 * Class Client
 * @property int $client_id 客户端ID
 * @property int $user_id 所属用户ID
 * @property string $identifier 客户端标识
 * @property string $name 客户端名称
 * @property string $secret 客户端密钥
 * @property string $redirect_uri 回调域
 * @property string $token_type
 * @property string $grant_type
 * @property int $status 状态
 * @property string $created_at
 * @property string $updated_at
 *
 * @package yuncms\oauth2\models
 */
class Client extends ActiveRecord implements ClientEntityInterface
{
    //状态
    const STATUS_DISABLED = 0;
    const STATUS_ACTIVE = 1;

    const GRANT_TYPE_AUTHORIZATION_CODE = 1;
    const GRANT_TYPE_IMPLICIT = 2;
    const GRANT_TYPE_PASSWORD = 3;
    const GRANT_TYPE_CLIENT_CREDENTIALS = 4;

    /**
     * Token 类别
     */
    const TOKEN_TYPE_BEARER = AccessToken::TYPE_BEARER;
    const TOKEN_TYPE_MAC = AccessToken::TYPE_MAC;

    /**
     * @var ResponseTypeInterface
     */
    private $_responseType;

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
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'grant_type', 'redirect_uri'], 'required'],
            [['grant_type', 'created_at', 'updated_at'], 'integer'],
            [['client_secret'], 'string', 'max' => 80],
            [['redirect_uri'], 'string', 'max' => 2000],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['grant_type', 'in', 'range' => [
                self::GRANT_TYPE_AUTHORIZATION_CODE,
                self::GRANT_TYPE_IMPLICIT,
                self::GRANT_TYPE_PASSWORD,
                self::GRANT_TYPE_CLIENT_CREDENTIALS
            ]],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DISABLED]],
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
                $this->setAttribute('identifier', Yii::$app->security->generateRandomString(80));
                $this->setAttribute('client_secret', Yii::$app->security->generateRandomString());
            }
            return true;
        } else {
            return false;
        }
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
        return $this->identifier;
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

    /**
     * 获取响应类别
     * @return BearerTokenResponse|MacTokenResponse|ResponseTypeInterface
     */
    public function getResponseType()
    {
        if (!$this->_responseType instanceof ResponseTypeInterface) {
            if (isset($this->token_type) && $this->token_type === static::TOKEN_TYPE_MAC) {
                $this->_responseType = new MacTokenResponse();
            } else {
                $this->_responseType = new BearerTokenResponse();
            }
        }
        return $this->_responseType;
    }

    public static function getGrantTypeId($grantType, $default = null)
    {
        return ArrayHelper::getValue(array_flip(static::grants()), $grantType, $default);
    }
}