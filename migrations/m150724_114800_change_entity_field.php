<?php

use yii\db\Schema;
use yii\db\Migration;

class m150724_114800_change_entity_field extends Migration
{
    public function up()
    {
        $this->alterColumn('{{%menu_item}}', 'entity', Schema::TYPE_STRING.'(255) NOT NULL DEFAULT \'page\'');
    }

    public function down()
    {
        echo "m150724_114800_change_entity_field cannot be reverted.\n";

        return false;
    }
}
