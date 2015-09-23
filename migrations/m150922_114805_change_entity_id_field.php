<?php

use yii\db\Schema;
use yii\db\Migration;

class m150922_114805_change_entity_id_field extends Migration
{
    public function up()
    {
        $this->alterColumn('{{%menu_item}}', 'entity_id', Schema::TYPE_STRING.'(50) NOT NULL');
    }

    public function down()
    {
        echo "m150922_114805_change_entity_id_field cannot be reverted.\n";

        return false;
    }
}
