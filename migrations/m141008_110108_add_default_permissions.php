<?php

use yii\db\Schema;
use yii\db\Migration;

class m141008_110108_add_default_permissions extends Migration
{
    public function up()
    {
        // Create the auth items
        $this->insert('{{%auth_item}}', [
            'name'          => 'showMenuModule',
            'type'          => 2,
            'description'   => 'Show menu module in main-menu',
            'created_at'    => time(),
            'updated_at'    => time()
        ]);
        
        $this->insert('{{%auth_item}}', [
            'name'          => 'createMenu',
            'type'          => 2,
            'description'   => 'Create a new menu in the menu module',
            'created_at'    => time(),
            'updated_at'    => time()
        ]);
        
        // Create the auth item relation
        $this->insert('{{%auth_item_child}}', [
            'parent'        => 'Superadmin',
            'child'         => 'showMenuModule'
        ]);
        
        $this->insert('{{%auth_item_child}}', [
            'parent'        => 'Superadmin',
            'child'         => 'createMenu'
        ]);
    }

    public function down()
    {
        // Delete the auth item relation
        $this->delete('{{%auth_item_child}}', [
            'parent'        => 'Superadmin',
            'child'         => 'showMenuModule'
        ]);
        
        $this->delete('{{%auth_item_child}}', [
            'parent'        => 'Superadmin',
            'child'         => 'createMenu'
        ]);
        
        // Delete the auth items
        $this->delete('{{%auth_item}}', [
            'name'          => 'showMenuModule',
            'type'          => 2,
        ]);
        
        $this->delete('{{%auth_item}}', [
            'name'          => 'createMenu',
            'type'          => 2,
        ]);
    }
}
