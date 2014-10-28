<?php

namespace infoweb\menu;

use Yii;
use yii\base\Event;
use yii\db\ActiveRecord;
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
        // Set eventhandlers for the 'MenuItem' model
        Event::on(MenuItem::className(), ActiveRecord::EVENT_AFTER_DELETE, function ($event) {
            
            // Delete the childre
            if (!$event->sender->deleteChildren())
                throw new \yii\base\Exception(Yii::t('app', 'Error while deleting this item'));
        });    
    }
}