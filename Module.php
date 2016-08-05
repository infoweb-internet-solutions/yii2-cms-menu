<?php

namespace infoweb\menu;

use Yii;
use yii\base\Event;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

use infoweb\menu\models\Menu;
use infoweb\menu\models\MenuItem;
use infoweb\pages\models\Page;

class Module extends \yii\base\Module
{
    public $controllerNamespace = 'infoweb\menu\controllers';

    /**
     * Enable the possibility to toggle the public visibility of menu-items
     * @var boolean
     */
    public $enablePrivateMenuItems = false;

    /**
     * The default value for the public visibility of a menu-item
     * @var boolean
     */
    public $defaultPublicVisibility = true;

    /**
     * Allow content duplication with the "duplicateable" plugin
     * @var boolean
     */
    public $allowContentDuplication = true;

    /**
     * Add button to create entity in menu item.
     * @var boolean
     */
    public $createEntityFromMenuItem = true;

    /**
     * The entities that a menu-item can point to
     * @var array
     */
    public $linkableEntities = [];

    public function init()
    {
        parent::init();

        // Merge the linkable entities with default values
        $this->linkableEntities = ArrayHelper::merge([
            MenuItem::className() => [
                'label' => 'Menu item',
                'i18nGroup' => 'infoweb/menu',
                'createEntity' => false,
                'createEntityUrl' => ''
            ],
            Page::className() => [
                'label' => 'Page',
                'i18nGroup' => 'infoweb/pages',
                'createEntity' => true,
                'createEntityUrl' => '/pages/page/create'
            ]
        ], $this->linkableEntities);

        // Set eventhandlers
        $this->setEventHandlers();

        // Content duplication is only possible if there is more than 1 app language
        if (isset(Yii::$app->params['languages']) && count(Yii::$app->params['languages']) == 1) {
            $this->allowContentDuplication = false;
        }
    }
    
    /**
     * return all needed configuration parameters to javascript.
     */
    public function getCkeditorEntitylinkConfiguration() {
        return [
            'linkableEntities' => $this->findLinkableEntities(),
            'url' => [
                'getEntityUrl' => Url::toRoute('/menu/menu-item/get-entity-url'),
                'getEntities' => Url::toRoute('/menu/menu-item/get-entities')
            ],
            'translations' => [
                'choose' => Yii::t('app', 'Maak een keuze'),
                'no_url' => Yii::t('app', 'Geef de link van de URL')
            ]
        ];
    }

    /**
     * Returns all the entities that can be linked to a menu-item
     *
     * @return  array
     */
    public function findLinkableEntities()
    {
        $linkableEntities = [];

        foreach ($this->linkableEntities as $k => $entity) {
            $entityModel = Yii::createObject($k);

            // The entityModel must have the 'getUrl' and 'getAllForDropDownList' methods
            if (method_exists($entityModel, 'getUrl') && method_exists($entityModel, 'getAllForDropDownList')) {                
                $linkableEntities[$k] = [
                    'label' => Yii::t($entity['i18nGroup'], $entity['label']),
                    'createEntity' => (isset($entity['createEntity'])) ? (bool) $entity['createEntity'] : false,
                    'createEntityUrl' => (isset($entity['createEntityUrl'])) ? (string) $entity['createEntityUrl'] : ''
                ];
            }
        }

        return $linkableEntities;
    }

    public function setEventHandlers()
    {
        // Set eventhandlers for the 'Menu' model
        Event::on(Menu::className(), ActiveRecord::EVENT_AFTER_DELETE, function ($event) {

            // Delete the children
            if (!$event->sender->deleteChildren()) {
                throw new \yii\base\Exception(Yii::t('app', 'There was an error while deleting this item'));
            }
        });

        // Set eventhandlers for the 'MenuItem' model
        Event::on(MenuItem::className(), ActiveRecord::EVENT_AFTER_DELETE, function ($event) {
            
            // Delete the children
            if (!$event->sender->deleteChildren()) {
                throw new \yii\base\Exception(Yii::t('app', 'There was an error while deleting this item'));
            }
        });    
    }
}