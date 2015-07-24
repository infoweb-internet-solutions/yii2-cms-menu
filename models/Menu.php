<?php

namespace infoweb\menu\models;

use Yii;
use yii\validators\NumberValidator;
use infoweb\menu\models\MenuItem;
use yii\db\Query;
use yii\helpers\Url;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "menu".
 *
 * @property string $id
 * @property string $name
 * @property string $created_at
 * @property string $updated_at
 */
class Menu extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'menu';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'max_level'], 'required'],
            ['max_level', 'integer'],
            [['name'], 'string', 'max' => 255]
        ];
    }

    public function behaviors()
    {
        return [
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
    public function attributeLabels()
    {
        return [
            'name' => Yii::t('app', 'Name'),
        ];
    }

    /**
     * Sortable tree
     */
    public static function sortableTree($settings = [])
    {
        $default_settings = array(
            'parent'    => NULL,
            'items'     => [],
            'menu-id'   => 0,
        );

        $settings = array_merge($default_settings, $settings);

        // Add new validator
        if (!isset($validator))
            $validator = new NumberValidator();

        // Get all menu items when parent is null
        if (!$validator->validate($settings['parent']))
            $settings['items'] = MenuItem::find()->where(['menu_id' => $settings['menu-id']])->orderby('position ASC')->all();

        //
        $result = '<ol' . (($settings['parent'] == NULL) ? ' class="sortable">' : '>');

        foreach ($settings['items'] as $item)
        {
            if ($item->parent_id == (int) $settings['parent'])
            {
                ob_start();
                ?>

                <li id="list-<?php echo $item->id; ?>">
                <div>
                    <span class="sort">
                        <i class="fa fa-arrows"></i>
                    </span>
                    <?php echo $item->name; ?>
                    <span class="action-buttons">
                        <a href="<?= URL::to(['update', 'id' => $item->id]) ?>" data-toggle="tooltip" title="<?= Yii::t('app', 'Update') ?>" data-pjax="0">
                            <span class="glyphicon glyphicon-pencil"></span>
                        </a>
                        <a href="<?= Url::to(['delete', 'id' => $item->id]) ?>" id="delete-<?php echo $item->id; ?>" data-toggle="tooltip" title="<?= Yii::t('app', 'Delete') ?>" data-method="post" data-confirm="<?php echo Yii::t('app', 'Are you sure you want to delete this item?'); ?>" data-pjax="0">
                            <span class="glyphicon glyphicon-trash"></span>
                        </a>                            
                        <a href="#" data-toggle-active-menu-items="<?php echo $item->id; ?>" data-toggle="tooltip" data-pjax="0" title="<?= Yii::t('app', 'Toggle active') ?>">
                            <?php if ($item->active == 1) : ?>
                                <span class="glyphicon glyphicon-eye-open"></span>
                            <?php else : ?>
                                <span class="glyphicon glyphicon-eye-close"></span>
                            <?php endif; ?>
                        </a>
                        
                        <?php if (Yii::$app->getModule('menu')->enablePrivateItems) : ?>
                        <a href="#" data-item="<?php echo $item->id; ?>" data-uri="<?= Url::toRoute('menu-item/public') ?>" data-toggle="tooltip" data-pjax="0" title="<?= Yii::t('infoweb/menu', 'Toggle public visiblity') ?>" class="toggle-public">
                            <?php if ($item->public == 1) : ?>
                                <span class="glyphicon glyphicon-lock icon-disabled"></span>
                            <?php else : ?>
                                <span class="glyphicon glyphicon-lock"></span>
                            <?php endif; ?>
                        </a>    
                        <?php endif; ?>
                        
                        <a href="<?php echo Yii::getAlias('@baseUrl') . '/' . MenuItem::findOne($item->id)->getUrl(true, true); ?>" data-toggle="tooltip" target="_blank" title="<?= Yii::t('app', 'View') ?>">
                            <span class="glyphicon glyphicon-globe"></span>
                        </a>
                        <?php if ($item->entity == MenuItem::ENTITY_PAGE): ?>
                        <a href="<?= Url::toRoute(['/pages/page/update', 'id' => $item->entity_id, 'referrer' => 'menu-items']) ?>" data-toggle="tooltip" title="<?= Yii::t('app', 'Edit {modelClass}', ['modelClass' => strtolower(Yii::t('infoweb/pages', 'Page'))]) ?>">
                            <span class="glyphicon glyphicon-book"></span>
                        </a>
                        <?php endif; ?>
                    </span>
                </div>

                <?php
                $result .= ob_get_clean();

                if (Menu::has_children($item->id))
                    $result .= Menu::sortableTree([
                        'parent'    => $item->id,
                        'items'     => $settings['items'],
                        'menu-id'   => $settings['menu-id'],
                    ]);

                $result .= "</li>";
            }
        }

        $result .= "</ol>";

        return $result;
    }

    /**
     * Menu item has children
     */
    public static function has_children($parent = NULL)
    {
        if ($parent === NULL)
            return false;

        $q = new Query();
        $result =  $q->select('`id`')
            ->from(MenuItem::tableName())
            ->where("`parent_id` = {$parent}")
            ->count('id');

        return ($result > 0) ? true : false;
    }

    /**
     * Render level select html
     *
     * @param array $settings
     * @return string
     */
    public static function level_select($settings = [])
    {
        $default_settings = [
            'active-only'   => FALSE,
            'parent'        => 0,
            'relations'     => array(),
            'menu-id'     => 0,
            'ancestor'      => 0,
            'active-ancestor' => 0,
            'selected'      => 0
        ];

        $settings = array_merge($default_settings, $settings);
        $result = '';
        $items = MenuItem::find()->where(['menu_id' => $settings['menu-id']])->orderby('position ASC')->all();

        foreach ($items as $item)
        {
            if ($settings['parent'] == $item->parent_id)
            {
                ob_start();
                ?>
                <option
                    value="<?php echo $item->id; ?>"
                    <?php if ($settings['selected'] != 0 && $settings['selected'] == $item->id) : ?> selected="selected"<?php endif; ?>
                    <?php /*if (in_array($item->id, Menu::findChildren(['id' => $settings['active-ancestor'], 'menu-id' => $settings['menu-id']]))) : ?> disabled="disabled"<?php endif; */?>>
                    <?php echo str_repeat("-", 2 * $item->level); ?><?php if ($item->level != 0) : ?>> <?php endif; ?><?php echo $item->name; ?>
                </option>
                <?php
                $result .= ob_get_clean();

                if (Menu::has_children($item->id))
                {
                    $result .= Menu::level_select(array(
                        'active-only'       => $settings['active-only'],
                        'parent'            => $item->id,
                        'relations'         => $settings['relations'],
                        'menu-id'           => $settings['menu-id'],
                        'ancestor'          => $item->parent_id,
                        'active-ancestor'   => $settings['active-ancestor'],
                        'selected'          => $settings['selected']
                    ));
                }
            }
        }

        return $result;
    }

    /**
     * Get all children by id
     *
     * @param int $id
     * @return array
     */
    protected static function findChildren($settings = [])
    {
        $default_settings = [
            'id'        => 0,
            'ids'       => [],
            'menu-id'   => 0
        ];

        $settings = array_merge($default_settings, $settings);

        if (!in_array($settings['id'], $settings['ids']))
            $settings['ids'][] = $settings['id'];

        $query = new Query;

        $results = $query->select('id')
            ->from(MenuItem::tableName())
            ->where(['parent_id' => $settings['id'], 'menu_id' => $settings['menu-id']])
            ->all();

        foreach ($results as $result)
        {
            $settings['ids'][] = $result['id'];
            $settings['ids'] = Menu::findChildren([
                'id'        => $result['id'],
                'ids'       => $settings['ids'],
                'menu-id'   => $settings['menu-id'],
            ]);
        }

        return $settings['ids'];
    }

    /**
     *
     *
     */
    public function menu_items_select($settings = array())
    {
        $default_settings = array(
            'active-only'   => FALSE,
            'parent'        => 0,
            'relations'     => array(),
            'menu-item-id'     => 0,
            'ancestor'      => 0,
            'selected'      => 0
        );

        $settings = array_merge($default_settings, $settings);
        $result = '';
        $items = MenuItem::find()->where(['menu_id' => $this->id, 'parent_id' => $settings['parent']])->orderby('position ASC')->all();

        foreach ($items as $item)
        {
            if ($settings['parent'] == $item->parent_id)
            {
                ob_start();
                ?>
                <option value="<?php echo $item->id; ?>"<?php if ($settings['menu-item-id'] == $item->id) : ?> disabled="disabled"<?php endif; ?><?php if ($settings['selected'] != 0 && $settings['selected'] == $item->id) : ?> selected="selected"<?php endif; ?>>
                    <?php echo str_repeat("-", 2 * $item->level); ?><?php if ($item->level != 0) : ?>> <?php endif; ?><?php echo $item->name; ?>
                </option>
                <?php
                $result .= ob_get_clean();

                if (Menu::has_children($item->id))
                {
                    $result .= $this->menu_items_select(array(
                        'active-only'   => $settings['active-only'],
                        'parent'        => $item->id,
                        'relations'     => $settings['relations'],
                        'menu-item-id'     => $settings['menu-item-id'],
                        'ancestor'      => $settings['ancestor'],
                        'selected'      => $settings['selected']
                    ));
                }
            }
        }

        return $result;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItems()
    {
        return $this->hasMany(MenuItem::className(), ['menu_id' => 'id']);
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
        return $this->hasMany(MenuItem::className(), ['menu_id' => 'id']);
    }
    
    /**
     * Returns the current max level
     * 
     * @return  int
     */
    public function getCurrentMaxLevel()
    {
        return (new Query())
                ->select('level')
                ->from('menu_item')
                ->where('menu_id = :menu_id', [':menu_id' => $this->id])
                ->max('level') + 1;   
    }
}