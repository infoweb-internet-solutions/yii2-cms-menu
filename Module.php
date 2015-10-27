<?php

namespace infoweb\menu;

use Yii;
use yii\base\Event;
use yii\db\ActiveRecord;
use infoweb\menu\models\Menu;
use infoweb\menu\models\MenuItem;

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
     * The entities that a menu-item can point to
     * @var array
     */
    public $linkableEntities = [];

    public function init()
    {
        parent::init();

        // Set eventhandlers
        $this->setEventHandlers();

        // Content duplication is only possible if there is more than 1 app language
        if (isset(Yii::$app->params['languages']) && count(Yii::$app->params['languages']) == 1) {
            $this->allowContentDuplication = false;
        }
    }
    
    public function setEventHandlers()
    {
        // Set eventhandlers for the 'Menu' model
        Event::on(Menu::className(), ActiveRecord::EVENT_AFTER_DELETE, function ($event) {
            
            // Delete the children
            if (!$event->sender->deleteChildren())
                throw new \yii\base\Exception(Yii::t('app', 'There was an error while deleting this item'));
        });
        
        // Set eventhandlers for the 'MenuItem' model
        Event::on(MenuItem::className(), ActiveRecord::EVENT_AFTER_DELETE, function ($event) {
            
            // Delete the children
            if (!$event->sender->deleteChildren())
                throw new \yii\base\Exception(Yii::t('app', 'There was an error while deleting this item'));
        });    
    }
}