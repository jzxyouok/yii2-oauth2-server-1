<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */
namespace yuncms\oauth2;

use Yii;

class Module extends \yii\base\Module
{
    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();

        if (!isset(Yii::$app->i18n->translations['oauth2*'])) {
            Yii::$app->i18n->translations['oauth2*'] = [
                'class' => 'yii\i18n\PhpMessageSource',
                'sourceLanguage' => 'en-US',
                'basePath' => __DIR__ . '/messages',
            ];
        }
    }
}