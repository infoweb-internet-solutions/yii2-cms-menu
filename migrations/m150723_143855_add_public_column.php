<?php

use yii\db\Schema;
use yii\db\Migration;

class m150723_143855_add_public_column extends Migration
{
    public function up()
    {
        $this->addColumn('{{%menu_item}}', 'public', Schema::TYPE_BOOLEAN.' UNSIGNED NOT NULL DEFAULT 1');
    }

    public function down()
    {
        $this->dropColumn('{{%menu_item}}', 'public');
    }
}
