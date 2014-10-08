<?php

namespace infoweb\menu\controllers;

use Yii;
use infoweb\menu\models\MenuItem;
use infoweb\menu\models\MenuItemLang;
use infoweb\menu\models\MenuItemSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use infoweb\menu\models\Menu;
use infoweb\pages\models\Page;
use yii\db\Query;
use yii\web\session;
use yii\web\AssetManager;

/**
 * MenuItemController implements the CRUD actions for MenuItem model.
 */
class MenuItemController extends Controller
{
    /**
     * @var boolean whether to enable CSRF validation for the actions in this controller.
     * CSRF validation is enabled only when both this property and [[Request::enableCsrfValidation]] are true.
     */
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['get'],
                    'position' => ['post'],
                    'active' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all MenuItem models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new MenuItemSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $menu = Menu::findone(Yii::$app->request->get('menu-id'));

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'menu' => $menu,
            'max_level' => json_encode($menu->max_level),
        ]);
    }

    /**
     * Displays a single MenuItem model.
     * @param string $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new MenuItem model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new MenuItem();

        // Get the active menu
        $menu = Menu::findone(Yii::$app->request->get('menu-id'));

        if (Yii::$app->request->getIsPost())
        {
            $post = Yii::$app->request->post();

            if (!$model->load($post)) {
                echo 'Model not loaded';
                exit();
            }

            if ($post['type'] == MenuItem::ENTITY_PAGE)
            {
                $model->entity = MenuItem::ENTITY_PAGE;
                $model->entity_id = $post['page_id'];
                $model->url = '';
            }
            elseif ($_POST['type'] == MenuItem::ENTITY_URL)
            {
                $model->entity = MenuItem::ENTITY_URL;
                $model->entity_id = 0;
                $model->url = $post['url'];
            }
            elseif ($_POST['type'] == MenuItem::ENTITY_MENU_ITEM)
            {
                $model->entity = MenuItem::ENTITY_MENU_ITEM;
                $model->entity_id = $post['menu_id'];
                $model->url = '';
            }

            // Parent is root
            if (empty($post['MenuItem']['parent_id']))
            {
                // Set parent and level
                $model->parent_id = 0;
                $model->level = 0;
            }
            else
            {
                // Load parent
                $parent = MenuItem::findOne($post['MenuItem']['parent_id']);

                // Set parent and level
                $model->parent_id	= $parent->id;
                $model->level		= $parent->level + 1;
            }

            // Set rest of attributes and save
            $model->position = $model->next_position();

            if (!$model->save()) {
                echo 'Model not saved';
                exit();
            }

            foreach (Yii::$app->params['languages'] as $k => $v) {

                // @todo Replace this code with:

                // change language
                // $model->language = 'fr';
                // $model->title = "French title";
                // save translation only
                //$tour->saveTranslation();

                $modelLang = $model->getTranslation($k);

                // nl-BE already exists after saving the model
                if (!isset($modelLang)) {
                    $modelLang = new MenuItemLang;
                }

                $modelLang->menu_item_id = $model->id;
                $modelLang->load($post[$k]);
                // @todo Remove this
                $modelLang->language = $post[$k]['MenuItemLang']['language'];

                if (!$modelLang->save()) {
                    echo 'Model lang not saved';
                    exit();
                }
            }

            // Set flash message
            $session = new Session;
            $session->setFlash('menu-item-success', "Menu item <strong>{$model->name}</strong> successfully created");

            if (isset($post['close'])) {
                return $this->redirect(['index', 'menu-id' => $model->menu_id]);
            } elseif (isset($post['new'])) {
                return $this->redirect(['create', 'menu-id' => $model->menu_id]);
            } else {
                return $this->redirect(['update', 'id' => $model->id, 'menu-id' => $model->menu_id]);
            }
        }

        // @todo Rewrite Query
        $q = new Query();
        $results =  $q->select('`p`.`id`, `pl`.`title`')
            ->from('`pages` AS `p`')
            ->innerjoin('`pages_lang` AS `pl`', '`p`.`id` = `pl`.`page_id`')
            ->where("`pl`.`language` = '" . Yii::$app->language . "'")
            ->orderBy('`pl`.`title`')
            ->all();

        foreach ($results as $result)
        {
            $pages[$result['id']] = $result['title'];
        }

        return $this->render('create', [
            'model' => $model,
            'level_select' => Menu::findOne($menu->id)->level_select(['menu-id' => Yii::$app->request->get('menu-id')]),
            'menu' => $menu,
            'pages' => $pages,
        ]);

    }

    /**
     * Updates an existing MenuItem model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        // Get the active menu
        $menu = Menu::findone(Yii::$app->request->get('menu-id'));

        if (Yii::$app->request->getIsPost())
        {
            $post = Yii::$app->request->post();

            if (!$model->load($post)) {
                echo 'Model not loaded';
                exit();
            }

            if ($post['type'] == MenuItem::ENTITY_PAGE)
            {
                $model->entity = MenuItem::ENTITY_PAGE;
                $model->entity_id = $post['page_id'];
                $model->url = '';
            }
            elseif ($_POST['type'] == MenuItem::ENTITY_URL)
            {
                $model->entity = MenuItem::ENTITY_URL;
                $model->entity_id = 0;
                $model->url = $post['url'];
            }
            elseif ($_POST['type'] == MenuItem::ENTITY_MENU_ITEM)
            {
                $model->entity = MenuItem::ENTITY_MENU_ITEM;
                $model->entity_id = $post['menu_id'];
                $model->url = '';
            }

            $current_parent = $model->parent_id;

            // Parent is root
            if (empty($post['MenuItem']['parent_id']))
            {
                // Set parent and level
                $model->parent_id = 0;
                $model->level = 0;
            }
            else
            {
                // Load parent
                $parent = MenuItem::findOne($post['MenuItem']['parent_id']);

                // Set parent and level
                $model->parent_id	= $parent->id;
                $model->level		= $parent->level + 1;
            }

            // Set rest of attributes and save
            if ($current_parent != $model->parent_id)
                $model->position = $model->next_position();

            if (!$model->save()) {
                echo 'Model not updated';
                exit();
            }

            foreach (Yii::$app->params['languages'] as $k => $v) {

                // @todo Replace this code with:

                // change language
                // $model->language = 'fr';
                // $model->title = "French title";
                // save translation only
                //$tour->saveTranslation();

                $modelLang = $model->getTranslation($k);
                $modelLang->menu_item_id = $model->id;
                $modelLang->load($post[$k]);

                if (!$modelLang->save()) {
                    echo 'ModelLang not updated';
                    exit();
                }
            }

            // Set flash message
            $session = new Session;
            $session->setFlash('menu-item-success', "Menu item <strong>{$model->name}</strong> successfully updated");

            if (isset($post['close'])) {
                return $this->redirect(['index', 'menu-id' => $model->menu_id, 'anchor' => 'list-' . $model->id]);
            } elseif (isset($post['new'])) {
                return $this->redirect(['create', 'menu-id' => $model->menu_id]);
            } else {
                return $this->redirect(['update', 'id' => $model->id, 'menu-id' => $model->menu_id]);
            }

        }

        // @todo Rewrite Query
        $q = new Query();
        $results =  $q->select('`p`.`id`, `pl`.`title`')
            ->from('`pages` AS `p`')
            ->innerjoin('`pages_lang` AS `pl`', '`p`.`id` = `pl`.`page_id`')
            ->where("`pl`.`language` = '" . Yii::$app->language . "'")
            ->orderBy('`pl`.`title`')
            ->all();

        foreach ($results as $result)
        {
            $pages[$result['id']] = $result['title'];
        }

        return $this->render('update', [
            'model' => $model,
            'level_select' => Menu::findOne($menu->id)->level_select(['selected' => $model->parent_id, 'active-ancestor' => $model->id, 'menu-id' => Yii::$app->request->get('menu-id')]),
            'menu' => $menu,
            'pages' => $pages,
        ]);
    }

    /**
     * Deletes an existing MenuItem model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        //return $this->redirect(['index', 'menu-id' => Yii::$app->request->get('menu-id')]);

        $model = $this->findModel($id);

        // Set flash message
        $session = new Session;

        if (!$model->delete())
        {
            $session->setFlash('menu-item-error', "Menu item <strong>{$model->name}</strong> not deleted");
        } else {
            $session->setFlash('menu-item-success', "Menu item <strong>{$model->name}</strong> successfully deleted");
        }

        return $this->redirect(['index', 'menu-id' => Yii::$app->request->get('menu-id')]);
    }

    /**
     * Saves the new positions of the menu items
     * @return mixed
     */
    public function actionPosition()
    {
        try {

            $post = Yii::$app->request->post();

            if(!isset($post['ids']))
                throw new Exception('Ongeldige menu items');

            // Delete first item because of bug in nestedsortable
            array_shift($post['ids']);

            $positions = array();

            foreach ($post['ids'] as $k => $v)
            {
                $item_id = $v['item_id'];
                $parent_id = $v['parent_id'];

                // Create menu item
                $menu_item = MenuItem::findOne($item_id);

                // Parent is root menu item
                if($parent_id == 'root')
                {
                    $menu_item->parent_id = 0;
                } else {
                    $menu_item->parent_id = $parent_id;
                }

                $menu_item->level = $v['depth'] - 1;

                if (!isset($positions["{$menu_item->parent_id}-{$menu_item->level}"]))
                    $positions["{$menu_item->parent_id}-{$menu_item->level}"] = 0;

                // Set rest of attributes and save
                $menu_item->position = $positions["{$menu_item->parent_id}-{$menu_item->level}"] + 1;
                $positions["{$menu_item->parent_id}-{$menu_item->level}"]++;

                if (!$menu_item->save())
                    throw new Exception("Fout bij het opslaan");
            }
        } catch (ErrorException $e) {
            Yii::error($e->getMessage());
        }

        $data['status'] = 1;

        Yii::$app->response->format = 'json';
        return $data;
    }

    /**
     * Finds the MenuItem model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return MenuItem the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = MenuItem::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * Set active state
     * @param string $id
     * @return mixed
     */
    public function actionActive()
    {
        $model = $this->findModel(Yii::$app->request->post('id'));
        $model->active = ($model->active == 1) ? 0 : 1;

        $data['status'] = $model->save();
        $data['active'] = $model->active;

        Yii::$app->response->format = 'json';
        return $data;
    }
}
