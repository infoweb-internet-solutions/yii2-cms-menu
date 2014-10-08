<?php

use yii\db\Schema;
use yii\db\Migration;

class m141008_091012_init extends Migration
{
    public function up()
    {
        $tableOptions = null;

        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        // Create 'menu' table
        $this->createTable('{{%menu}}', [
            'id'                    => Schema::TYPE_PK,
            'name'                  => Schema::TYPE_STRING . '(255) NOT NULL',
            'max_level'             => 'TINYINT(3) UNSIGNED NOT NULL DEFAULT \'4\'',
            'created_at'            => Schema::TYPE_INTEGER . ' UNSIGNED NOT NULL',
            'updated_at'            => Schema::TYPE_INTEGER . ' UNSIGNED NOT NULL',
        ], $tableOptions);

        // Create 'menu_item' table
        $this->createTable('{{%menu_item}}', [
            'id'                    => Schema::TYPE_PK,
            'menu_id'               => Schema::TYPE_INTEGER . ' NOT NULL',
            'parent_id'             => Schema::TYPE_INTEGER . ' UNSIGNED NOT NULL',
            'entity'                => "ENUM('page','menu-item', 'url') NOT NULL DEFAULT 'page'",
            'entity_id'             => Schema::TYPE_INTEGER . ' UNSIGNED NOT NULL',
            'level'                 => 'TINYINT(3) UNSIGNED NOT NULL DEFAULT \'0\'',
            'url'                   => Schema::TYPE_STRING . '(255) NOT NULL',
            'position'              => 'TINYINT(3) UNSIGNED NOT NULL DEFAULT \'0\'',
            'active'                => 'TINYINT(3) UNSIGNED NOT NULL DEFAULT \'1\'',
            'created_at'            => Schema::TYPE_INTEGER . ' UNSIGNED NOT NULL',
            'updated_at'            => Schema::TYPE_INTEGER . ' UNSIGNED NOT NULL',
        ], $tableOptions);
        
        // Create indexes on the 'menu_item' table
        $this->createIndex('menu_id', '{{%menu_item}}', 'menu_id');
        $this->createIndex('parent_id', '{{%menu_item}}', 'parent_id');
        $this->createIndex('entity', '{{%menu_item}}', 'entity');
        $this->createIndex('entity_id', '{{%menu_item}}', 'entity_id');
        $this->addForeignKey('FK_MENU_ITEM_MENU_ID', '{{%menu_item}}', 'menu_id', '{{%menu}}', 'id', 'CASCADE', 'RESTRICT');

        // Create 'menu_item_lang' table
        $this->createTable('{{%menu_item_lang}}', [
            'menu_item_id'               => Schema::TYPE_INTEGER . ' NOT NULL',
            'language'              => Schema::TYPE_STRING . '(2) NOT NULL',
            'name'                  => Schema::TYPE_STRING . '(255) NOT NULL',
            'created_at'            => Schema::TYPE_INTEGER . ' UNSIGNED NOT NULL',
            'updated_at'            => Schema::TYPE_INTEGER . ' UNSIGNED NOT NULL'
        ], $tableOptions);
       
        // Create indexes on the 'menu_item_lang' table
        $this->addPrimaryKey('menu_item_menu_id_language', '{{%menu_item_lang}}', ['menu_item_id', 'language']);
        $this->createIndex('language', '{{%menu_item_lang}}', 'language');
        $this->addForeignKey('FK_MENU_ITEM_LANG_MENU_ITEM_ID', '{{%menu_item_lang}}', 'menu_item_id', '{{%menu_item}}', 'id', 'CASCADE', 'RESTRICT');
    }

    public function down()
    {
        $this->dropTable('menu_item_lang');
        $this->dropTable('menu_item');
        $this->dropTable('menu');
    }
}
