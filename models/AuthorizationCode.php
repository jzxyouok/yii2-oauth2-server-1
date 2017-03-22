<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */
namespace yuncms\oauth2\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\VarDumper;
use yii\base\Exception;
//use yuncms\oauth2\Exception;

/**
 * This is the model class for table "oauth_authorization_code".
 *
 * @property string $authorization_code
 * @property string $client_id
 * @property integer $user_id
 * @property string $redirect_uri
 * @property integer $expires
 * @property string $scope
 *
 * @property Client $client
 * @property \yuncms\user\models\User $user
 */
class AuthorizationCode extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%oauth2_authorization_code}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['authorization_code', 'client_id', 'user_id', 'expires'], 'required'],
            [['client_id','user_id', 'expires'], 'integer'],
            [['scope'], 'string'],
            [['authorization_code'], 'string', 'max' => 40],
            [['redirect_uri'], 'url'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'authorization_code' => 'Authorization Code',
            'app_id' => 'Client ID',
            'user_id' => 'User ID',
            'redirect_uri' => 'Redirect Uri',
            'expires' => 'Expires',
            'scope' => 'Scopes',
        ];
    }

    /**
     *
     * @param array $params
     * @throws Exception
     * @return \yuncms\oauth2\models\AuthorizationCode
     */
    public static function createAuthorizationCode(array $params)
    {
        static::deleteAll(['<', 'expires', time()]);

        $params['authorization_code'] = Yii::$app->security->generateRandomString(40);
        $authCode = new static($params);

        if ($authCode->save()) {
            return $authCode;
        } else {
            Yii::error(__CLASS__ . ' validation error: ' . VarDumper::dumpAsString($authCode->errors));
        }
        throw new Exception('Unable to create authorization code', Exception::SERVER_ERROR);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClient()
    {
        return $this->hasOne(Client::className(), ['client_id' => 'client_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Yii::$app->user->identityClass, ['id' => 'user_id']);
    }
}