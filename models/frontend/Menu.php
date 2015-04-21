<?php

namespace infoweb\menu\models\frontend;

use Yii;
use infoweb\menu\models\MenuItem;

/**
 * This is the frontend model class for table "menu".
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
        if (isset($options['parentId']))
            $items = $items->andWhere(['parent_id' => $options['parentId']]);
        
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
    public function getTree($settings = ['parentId' => 0, 'level' => 0, 'includeLanguage' => true])
    {
        $items = [];
        $menuItems = $this->getItems($settings)->all();

        foreach ($menuItems as $menuItem) {
            
            // First we need to check if the item has a non-public page attached
            // If so, and no user is logged in, the item is skipped
            if ($menuItem->entity == MenuItem::ENTITY_PAGE && Yii::$app->user->isGuest) {
                $menuItemEntity = $menuItem->entityModel;
                
                if ($menuItemEntity->public == false)
                    continue;                   
            }
            
            $menuItem->language = Yii::$app->language;
            
            $url = $menuItem->getUrl($settings['includeLanguage']);

            $item = [
                'label'     => $menuItem->name,
                'url'       => $url,
                'active'    => Yii::$app->request->url == $url
            ];
            
            // Get the item's children
            $children = $this->getTree([
                'parentId'          => $menuItem->id,
                'level'             => $menuItem->level + 1,
                'includeLanguage'   => $settings['includeLanguage']
            ]);
            
            if ($children)
                $item['items'] = $children;
            
            $items[] = $item;
        }
        
        return $items;
    }
}