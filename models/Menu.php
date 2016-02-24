<?php

namespace infoweb\menu\models;

use Yii;
use yii\validators\NumberValidator;
use infoweb\menu\models\MenuItem;
use yii\db\Query;
use yii\helpers\Url;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

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
            'selected'      => 0,
            'active-menu-item-id' => 0,
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

                    <?php if (in_array($item->id, Menu::findChildren(['id' => $settings['active-menu-item-id'], 'menu-id' => $settings['menu-id']]))) : ?>
                        disabled="disabled"
                    <?php endif; ?>>
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
                        'selected'          => $settings['selected'],
                        'active-menu-item-id' => $settings['active-menu-item-id'],
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
     * Returns all the children with a specific parent
     * @return \yii\db\ActiveQuery
     */
    public function getChildrenWithParent($parentId = 0)
    {
        return $this->getChildren()->where(['parent_id' => $parentId])->orderBy(['position' => SORT_ASC]);
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

    /**
     * Returns descendants of the provided parent in a nestable tree structure
     *
     * @param   int     $parentId
     * @return  array
     */
    public function getNestableTree($parentId = 0, &$tree = [])
    {
        // Find the direct descendants of the provided parent
        $descendants = $this->getChildrenWithParent($parentId)->all();

        foreach ($descendants as $k => $descendant) {
            $data = ['item' => $descendant];

            // Load the children of the descendant as a nestable tree
            if ($descendant->children) {
                $data['children'] = $this->getNestableTree($descendant->id);
            }

            $tree[] = $data;
        }

        return $tree;
    }

    /**
     * Returns all items formatted for usage in a Html::dropDownList widget:
     *      [
     *          'id' => 'name',
     *          'id' => 'name,
     *          ...
     *      ]
     *
     * @return  array
     */
    public function getAllForDropDownList($parent = 0)
    {
        $items = [];
        $children = $this->getChildren()->where(['parent_id' => $parent])->orderBy(['position' => SORT_ASC])->all();

        foreach ($children as $child) {
            $items[$child->id] = $child->name;

            if ($child->children) {
                $items = $child->getChildrenForDropDownList($items);
            }
        }

        return $items;
    }

    public function getAllForLevelDropDownList()
    {
        return ArrayHelper::merge([0 => 'Root'], $this->getAllForDropDownList());
    }
}