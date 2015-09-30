<?php

use yii\db\Schema;
use yii\db\Migration;

class m150929_071515_add_type_column extends Migration
{
    public function up()
    {
        $this->addColumn('{{%menu_item}}', 'type', "ENUM('system','user-defined') NOT NULL DEFAULT 'user-defined'");
    }

    public function down()
    {
        $this->dropColumn('{{%menu_item}}', 'type');
    }
}
