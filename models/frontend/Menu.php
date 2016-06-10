<?php

namespace infoweb\menu\models\frontend;

use Yii;
use infoweb\menu\models\MenuItem;
use infoweb\pages\models\Page;

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
        if (Yii::$app->getModule('menu') && Yii::$app->getModule('menu')->enablePrivateMenuItems && Yii::$app->user->isGuest)
            $items = $items->andWhere(['public' => 1]);

        return $items->orderBy('position', 'ASC');
    }

    /**
     * Returns a tree of menu-items that belong to the menu
     *
     * @param   array   $settings       A settings array that holds the parent-id and level
     * @return  array
     */
    public function getTree($settings = ['subMenu' => true, 'parentId' => 0, 'level' => 0, 'includeLanguage' => true, 'convertBr' => false])
    {
        $items = [];
        $menuItems = $this->getItems($settings)->all();

        foreach ($menuItems as $menuItem) {

            // First we need to check if the item has a non-public page attached
            // If so, and no user is logged in, the item is skipped
            if ($menuItem->entity == Page::className() && Yii::$app->user->isGuest) {
                $menuItemEntity = $menuItem->entityModel;
                if (isset($menuItemEntity->public) && $menuItemEntity->public == false)
                    continue;
            }

            $item = [
                'label'     => (isset($settings['convertBr'])) ? str_replace("\n", '<br>', $menuItem->name) : str_replace("\n", ' ', $menuItem->name),
                'url'       => $menuItem->getUrl($settings['includeLanguage']),
                'entity'    => $menuItem->entity,
                'entity_id' => $menuItem->entity_id,
                // The item is active if it's (or that of it's entity in case
                // of redirect to another menu-item) id is in the array of the
                // menu-items that are linked to the current page
                'active'    => in_array(($menuItem->entity == MenuItem::className()) ? $menuItem->entity_id : $menuItem->id, Yii::$app->page->linkedMenuItemsIds),
                'options' => ['class' => "menu-item-{$menuItem->id}"],
            ];

            // A menu-item that links to an url has to open in a new window
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
                    'convertBr'         => isset($settings['convertBr']),
                ]);

                if ($children)
                    $item['items'] = $children;
            }

            $items[$menuItem->id] = $item;
        }

        return $items;
    }
}