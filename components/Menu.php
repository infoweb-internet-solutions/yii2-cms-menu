<?php

namespace infoweb\menu\components;

use Yii;
use yii\base\Component;

use infoweb\alias\models\AliasLang;
use infoweb\pages\models\Page;
use infoweb\menu\models\MenuItem;

class Menu extends Component {

    public $menuItem;

    /**
     * Returns a page, based on the alias that is provided in the request or, if
     *  no alias is provided, the homepage
     *
     * @return  Page
     */
    public function getPage()
    {
        // An alias is provided
        if (Yii::$app->request->get('alias')) {

            // Load the alias translation
            $aliasLang = AliasLang::findOne([
                'url'       => Yii::$app->request->get('alias'),
                'language'  => Yii::$app->language
            ]);

            if (!$aliasLang) {
                return Yii::$app->response->redirect('@web/404');
            }

            // Get the alias
            $alias = $aliasLang->alias;

            // Get the page
            $page = $alias->entityModel;

        } else {
            // Load the page that is marked as the 'homepage'
            $page = Page::findOne(['homepage' => 1]);
        }

        // The page must be active
        if ($page->active != 1) {
            return Yii::$app->response->redirect('@web/404');
        }

        // Current menu item

        $this->menuItem = MenuItem::findOne(['entity' => 'page', 'entity_id' => $page->id]);

        if ($this->menuItem) {

            // If current menu item has a parent id, the parent becomes the current menu item
            if ($this->menuItem->parent_id > 0) {

                $this->menuItem = MenuItem::findOne($this->menuItem->parent_id);
            }

            Yii::$app->params['menuId'] = $this->menuItem->id;
        }

        // Set the page language
        $page->language = Yii::$app->language;

        return $page;
    }

    public function getMenu()
    {
        $this->getPage();

        return $this->menuItem;
    }

}