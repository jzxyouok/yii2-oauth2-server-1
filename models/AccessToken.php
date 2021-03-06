<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */
namespace yuncms\oauth2\models;

use Yii;
use yii\db\ActiveRecord;
use League\OAuth2\Server\CryptTrait;
use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;

/**
 * Access Token Repository Interface
 * @package yuncms\oauth2\models
 */
class AccessToken extends ActiveRecord implements AccessTokenEntityInterface
{
    use CryptTrait;

    use AccessTokenTrait;

    const TYPE_BEARER = 1;
    const TYPE_MAC = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%oauth2_access_token}}';
    }

    /**
     * 定义应用和用户的关系
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Yii::$app->user->identityClass, ['id' => 'user_id']);
    }
}