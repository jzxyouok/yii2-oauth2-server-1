<?php

namespace yuncms\oauth2\migrations;

use yii\db\Migration;

class M170113100603Create_oauth2_client_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB  AUTO_INCREMENT=100000';
        }

        $this->createTable('{{%oauth2_client}}', [
            'client_id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull()->comment('所属用户ID'),
            'name' => $this->string()->notNull()->comment('客户端名称'),
            'identifier' => $this->string(80)->comment('客户端标识'),
            'client_secret' => $this->string(80)->notNull()->comment('密钥'),
            'redirect_uri' => $this->text()->notNull()->comment('回调URL'),
            'grant_type' => $this->smallInteger(),
            'domain' => $this->string()->comment('域名'),
            'provider' => $this->string()->comment('提供方'),
            'icp' => $this->string()->comment('ICP备案'),
            'status' => $this->string()->comment('状态'),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);
        $this->addforeignkey('fk_oauth2_client_user_id', '{{%oauth2_client}}', 'user_id', '{{%user}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%oauth2_client}}');
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
