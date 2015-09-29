<?php

use yii\db\Schema;
use yii\db\Migration;

class m150929_071512_add_params_column extends Migration
{
    public function up()
    {
        $this->addColumn('{{%menu_item_lang}}', 'params', Schema::TYPE_TEXT.' NOT NULL');
    }

    public function down()
    {
        $this->dropColumn('{{%menu_item_lang}}', 'params');
    }
}
