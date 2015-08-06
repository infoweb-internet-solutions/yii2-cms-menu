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
        if (isset($options['level'])) {

            $items = $items->andWhere(['level' => $options['level']]);
        }
        
        // Only show public items to guest users if this options is enabled in the "menu" module
        if (Yii::$app->getModule('menu') && Yii::$app->getModule('menu')->enablePrivateItems && Yii::$app->user->isGuest)
            $items = $items->andWhere(['public' => 1]);        

        return $items->orderBy('position', 'ASC');
    }
    
    /**
     * Returns a tree of menu-items that belong to the menu
     * 
     * @param   array   $settings       A settings array that holds the parent-id and level
     * @return  array
     */
    public function getTree($settings = ['subMenu' => true, 'parentId' => 0, 'level' => 0, 'includeLanguage' => true])
    {
        $items = [];

        $menuItems = $this->getItems($settings)->all();

        // Array for active menu items
        $activeItems = [Yii::$app->frontend->menuItem->id, Yii::$app->frontend->parentMenuItem->id];

        foreach ($menuItems as $menuItem) {
            
            // First we need to check if the item has a non-public page attached
            // If so, and no user is logged in, the item is skipped
            if ($menuItem->entity == MenuItem::ENTITY_PAGE && Yii::$app->user->isGuest) {
                $menuItemEntity = $menuItem->entityModel;
                
                if (isset($menuItemEntity->public) && $menuItemEntity->public == false)
                    continue;                   
            }
            
            $menuItem->language = Yii::$app->language;

            $active = false;

            // Extra check for menu item entity
            if ($menuItem->entity == MenuItem::ENTITY_MENU_ITEM ) {

                // Get the parent menu item of the entity
                $parent = MenuItem::find()->select('id')->where(['id' => $menuItem->entity_id])->column();

                // Add the parent id to the active items
                $activeItems[] = $parent;

                // Active is true if the entity id is in the active items array
                if (in_array($menuItem->entity_id, $activeItems)) {
                    $active = true;
                }
            }

            // Active is true if the menu item id is in the active items array
            if (in_array($menuItem->id, $activeItems)) {
                $active = true;
            }

            $item = [
                'label'     => $menuItem->name,
                'url'       => $menuItem->getUrl($settings['includeLanguage']),
                'active'    => $active,
            ];

            // Url entity
            if ($menuItem->entity == MenuItem::ENTITY_URL) {
                $item['linkOptions'] = [
                    'target' => '_blank',
                ];
            }

            if ($settings['subMenu'] == true) {
                // Get the item's children
                $children = $this->getTree([
                    'parentId'          => $menuItem->id,
                    'level'             => $menuItem->level + 1,
                    'includeLanguage'   => $settings['includeLanguage'],
                    'subMenu'           => $settings['subMenu'],
                ]);

                if ($children)
                    $item['items'] = $children;
            }

            $items[$menuItem->id] = $item;
        }
        
        return $items;
    }
}