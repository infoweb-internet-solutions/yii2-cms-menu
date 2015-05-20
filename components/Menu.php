<?php

namespace infoweb\menu\components;

use Yii;
use yii\base\Component;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\widgets\ActiveForm;

use infoweb\alias\models\AliasLang;
use infoweb\pages\models\Page;
use infoweb\menu\models\MenuItem;

class Menu extends Component {

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
                throw new \yii\web\HttpException(404, Yii::t('app', 'The requested page could not be found.'));
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
            throw new \yii\web\HttpException(404, Yii::t('app', 'The requested page could not be found.'));
        }

        // Current menu item
        $menuItem = MenuItem::findOne(['entity' => 'page', 'entity_id' => $page->id]);
        if ($menuItem) {

            // If current menu item has a parent id, the parent becomes the current menu item
            if ($menuItem->parent_id > 0) {
                $menuItem = MenuItem::findOne($menuItem->parent_id);
            }

            Yii::$app->params['menuId'] = $menuItem->id;
        }

        // Set the page language
        $page->language = Yii::$app->language;

        return $page;
    }

    public function menu()
    {

    }

}