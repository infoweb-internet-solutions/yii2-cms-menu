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

        foreach ($menuItems as $menuItem) {
            
            // First we need to check if the item has a non-public page attached
            // If so, and no user is logged in, the item is skipped
            if ($menuItem->entity == MenuItem::ENTITY_PAGE && Yii::$app->user->isGuest) {
                $menuItemEntity = $menuItem->entityModel;
                
                if (isset($menuItemEntity->public) && $menuItemEntity->public == false)
                    continue;                   
            }
            
            $menuItem->language = Yii::$app->language;            
            $url = $menuItem->getUrl($settings['includeLanguage']);
            $activeUrlWithoutMenuItemUrl = stripos(Yii::$app->request->url, $menuItem->getUrl(false));

            if ($activeUrlWithoutMenuItemUrl !== false) {
                $activeUrlWithoutMenuItemUrl = str_replace(Yii::$app->request->url, '', $menuItem->getUrl(false));
                $activeUrlWithoutMenuItemUrl = (strlen($activeUrlWithoutMenuItemUrl) > 0) ? $activeUrlWithoutMenuItemUrl[0] : $activeUrlWithoutMenuItemUrl;
            }

            $item = [
                'label'     => $menuItem->name,
                'url'       => $url,
                'active'    => stripos(Yii::$app->request->url, $menuItem->getUrl(false)) !== false, // ($activeUrlWithoutMenuItemUrl && in_array($activeUrlWithoutMenuItemUrl, ['', '/'])) ? true : false
            ];

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