<?php

namespace infoweb\menu\models\frontend;

use Yii;

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
            $menuItem->language = Yii::$app->language;
            
            $url = $menuItem->getUrl($settings['includeLanguage']);

            $item = [
                'label'     => $menuItem->name,
                'url'       => $url,
                'active'    => Yii::$app->request->url == $url
            ];

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

            $items[] = $item;
        }
        
        return $items;
    }
}