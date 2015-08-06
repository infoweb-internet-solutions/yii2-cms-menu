<?php

use yii\db\Schema;
use yii\db\Migration;

class m150806_071513_add_position_column extends Migration
{
    public function up()
    {
        $this->addColumn('{{%menu}}', 'position', Schema::TYPE_INTEGER.' UNSIGNED NOT NULL');
    }

    public function down()
    {
        $this->dropColumn('{{%menu}}', 'position');
    }
}
