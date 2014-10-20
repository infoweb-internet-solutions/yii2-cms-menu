<?php

namespace infoweb\menu\models;

use Yii;

/**
 * This is the model class for table "menu_item_lang".
 *
 * @property string $id
 * @property string $menu_item_id
 * @property string $language
 * @property string $name
 *
 * @property MenuItems $menu
 */
class MenuItemLang extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'menu_item_lang';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // Required
            [['language'], 'required'],
            // Only required for existing records
            [['menu_item_id'], 'required', 'when' => function($model) {
                return !$model->isNewRecord;
            }],
            // Only required for the app language
            [['name'], 'required', 'when' => function($model) {
                return $model->language == Yii::$app->language;
            }],
            // Trim
            [['name'], 'trim'],
            // Types
            [['menu_item_id', 'created_at', 'updated_at'], 'integer'],
            [['language'], 'string', 'max' => 2],
            [['name'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'menu_item_id' => Yii::t('infoweb/menu', 'Menu ID'),
            'language' => Yii::t('app', 'Language'),
            'name' => Yii::t('app', 'Name'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMenu()
    {
        return $this->hasOne(MenuItem::className(), ['id' => 'menu_item_id']);
    }
}
