<?php

namespace infoweb\menu\models;

use Yii;
use yii\validators;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\helpers\Url;
use dosamigos\translateable\TranslateableBehavior;
use infoweb\pages\models\Page;

/**
 * This is the model class for table "menu_item".
 *
 * @property string $id
 * @property string $menu_id
 * @property string $parent_id
 * @property string $entity
 * @property string $entity_id
 * @property string $level
 * @property string $name
 * @property string $url
 * @property integer $position
 * @property integer $active
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Menus $menu
 */
class MenuItem extends \yii\db\ActiveRecord
{
    // Entity types
    const ENTITY_PAGE = 'page';
    const ENTITY_URL = 'url';
    const ENTITY_MENU_ITEM = 'menu-item';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'menu_item';
    }

    public function behaviors()
    {
        return [
            'trans' => [
                'class' => TranslateableBehavior::className(),
                'translationAttributes' => [
                    'name'
                ]
            ],
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => 'updated_at',
                ],
                'value' => function() { return time(); },
            ],
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['menu_id', 'parent_id', 'level', 'position'], 'integer'],
            [['url', 'anchor'], 'string', 'max' => 255],
            // Required
            [['menu_id', 'parent_id', 'entity'], 'required'],
            // Only required when the entity is no url
            // @todo: Re-activate this
            /*[['entity_id'], 'required', 'when' => function($model) {
                return $model->entity != self::ENTITY_URL;
            }],*/
            // Trim
            [['url', 'anchor'], 'trim'],
            [['url'], 'required', 'when' => function($model) {
                return $model->entity == self::ENTITY_URL;
            }],
            [['url'], 'url', 'defaultScheme' => 'http'],
            [['entity_id'], 'default', 'value' => 0],
            /*['parent_id', function($attribute, $params) {
                mail('fabio@infoweb.be', __FILE__.' => '.__LINE__, '');
                if ($this->level > $model->menu->max_level - 1)
                    $this->addError($attribute, Yii::t('infoweb/menu', 'The maximum level has been reached'));    
            }]*/
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'menu_id' => Yii::t('infoweb/menu', 'Menu ID'),
            'parent_id' => Yii::t('infoweb/menu', 'Parent ID'),
            'entity' => Yii::t('app', 'Entity'),
            'entity_id' => Yii::t('app', 'Entity ID'),
            'level' => Yii::t('infoweb/menu', 'Level'),
            'name' => Yii::t('app', 'Name'),
            'url' => Yii::t('app', 'Url'),
            'anchor' => Yii::t('infoweb/menu', 'Anchor'),
            'position' => Yii::t('app', 'Position'),
            'active' => Yii::t('app', 'Active'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMenu()
    {
        return $this->hasOne(Menu::className(), ['id' => 'menu_id']);
    }

    /**
     * Get the next position
     *
     * @return int
     */
    public function nextPosition()
    {
        $query = new Query;

        $result = $query->select('IFNULL(MAX(`position`),0) + 1 AS `position`')
            ->from($this->tableName())
            ->where(['level' => $this->level, 'parent_id' => $this->parent_id, 'menu_id' => $this->menu_id])
            ->one();

        return $result['position'];
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTranslations()
    {
        return $this->hasMany(MenuItemLang::className(), ['menu_item_id' => 'id']);
    }

    /**
     * Returns the model of the entity that is associated with the item
     * 
     * @return  mixed
     */
    public function getEntityModel()
    {
        switch ($this->entity) {
            case self::ENTITY_PAGE:
            default:
                return Page::findOne($this->entity_id);
                break;
           
            case self::ENTITY_MENU_ITEM:  
                return MenuItem::findOne($this->entity_id);
                break;
                
            case self::ENTITY_URL:  
                return MenuItem::findOne($this->id);
                break;     
        }            
    }
    
    /**
     * Returns the url for the item
     * 
     * @param   boolean     A flag to determine if the language parameter should 
     *                      be added to the url
     * @param   boolean     A flag to determine if the url should be prefixed with
     *                      the webpath 
     * @return  string
     */
    public function getUrl($includeLanguage = true, $excludeWebPath = false)
    {
        if ($this->entity == self::ENTITY_URL) {
            return $this->url;
        } else {
            $prefix = (!$excludeWebPath) ? '@web/' : '';
            $prefix .= ($includeLanguage) ? "{$this->language}/" : '';

            if ($this->entity == self::ENTITY_PAGE) {
                $page = $this->getEntityModel();
            } else {
                $menuItem = $this->getEntityModel();
                if ($menuItem->entity != MenuItem::ENTITY_PAGE) {
                    $menuItem = $menuItem->getEntityModel();
                }

                $page = $menuItem->getEntityModel();
            }

            // In the frontend application, the alias for the homepage is ommited
            // and '/' is used
            if (Yii::$app->id == 'app-frontend' && $page->homepage == true) {
                return Url::to($prefix);
            }
            
            // An anchor is set, append it to the url
            if ($this->entity == self::ENTITY_PAGE && !empty($this->anchor)) {
                return Url::to(["{$prefix}{$page->alias->url}", '#' => $this->anchor]);
            }

            return Url::to("{$prefix}{$page->alias->url}");
        }
    }
    
    /**
     * Recursively deletes all children of the item
     * 
     * @return  boolean
     */
    public function deleteChildren()
    {
        foreach ($this->getChildren()->all() as $child) {
            if (!$child->delete())
                return false;   
        } 
        
        return true;   
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChildren()
    {
        return $this->hasMany(MenuItem::className(), ['parent_id' => 'id']);
    }
}
