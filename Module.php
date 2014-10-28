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

    public function init()
    {
        parent::init();

        // Set eventhandlers
        $this->setEventHandlers();
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