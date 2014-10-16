<?php

namespace infoweb\menu\models\frontend;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use infoweb\menu\models\MenuItem;

/**
 * This is the model class for table "menu".
 *
 * @property string $id
 * @property string $name
 * @property string $created_at
 * @property string $updated_at
 */
class Menu extends \infoweb\menu\models\Menu
{
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItems($options = [])
    {
        $items = parent::getItems()->where(['active' => 1]);
        
        // Filter by parent
        if (isset($options['parent-id']))
            $items = $items->andWhere(['parent_id' => $options['parent-id']]);
        
        // Filter by level
        if (isset($options['level']))
            $items = $items->andWhere(['level' => $options['level']]);
        
        return $items->orderBy('position', 'ASC');
    }
    
    /**
     * Returns a tree of menu-items that belong to the menu
     * 
     * @param   array   $settings       A settings array that holds the parent-id and level
     * @return  array
     */
    public function getTree($settings = ['parent-id' => 0, 'level' => 0])
    {
        $items = [];
        $menuItems = $this->getItems($settings)->all();
        
        foreach ($menuItems as $menuItem) {
            $menuItem->language = Yii::$app->language;
            $url = $menuItem->getUrl();
            
            $item = [
                'label'     => $menuItem->name,
                'url'       => $url,
                'active'    => (Yii::$app->request->url == $url) ? true : false 
            ];
            
            // Get the item's children
            $children = $this->getTree(['parent-id' => $menuItem->id, 'level' => $menuItem->level + 1]);
            
            if ($children)
                $item['items'] = $children;
            
            $items[] = $item;
        }
        
        return $items;
    }
}