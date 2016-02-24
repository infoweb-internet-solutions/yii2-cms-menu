<?php

namespace infoweb\menu\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\widgets\ActiveForm;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;
use infoweb\menu\models\MenuItem;
use infoweb\menu\models\MenuItemLang;
use infoweb\menu\models\MenuItemSearch;
use infoweb\menu\models\Menu;
use infoweb\pages\models\Page;

/**
 * MenuItemController implements the CRUD actions for MenuItem model.
 */
class MenuItemController extends Controller
{
    /**
     * The entity types that can be linked to a menu-item
     * @var array
     */
    protected $entityTypes = [];

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete'           => ['post'],
                    'position'         => ['post'],
                    'active'           => ['post'],
                    'update-positions' => ['post']
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
        // Store the active menu-id in a session var if it is provided through the url
        if (Yii::$app->request->get('menu-id') != null)
            Yii::$app->session->set('menu-items.menu-id', Yii::$app->request->get('menu-id'));

        $searchModel = new MenuItemSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $menu = $this->findActiveMenu();

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
            'menu'         => $menu,
            'maxLevel'     => json_encode($menu->max_level),
        ]);
    }

    /**
     * Creates a new MenuItem model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        // Initialize the menu-item with default values
        $model = new MenuItem([
            'menu_id'   => $this->findActiveMenu()->id,
            'active'    => 1,
            'public'    => (int) $this->module->defaultPublicVisibility,
            'type'      => MenuItem::TYPE_USER_DEFINED,
        ]);

        // The view params
        $params = $this->getDefaultViewParams($model);

        if (Yii::$app->request->getIsPost()) {

            $post = Yii::$app->request->post();

            // Ajax request, validate
            if (Yii::$app->request->isAjax) {
                return $this->validateModel($model, $post);
            // Normal request, save
            } else {
                return $this->saveModel($model, $post);
            }
        }

        return $this->render('create', $params);
    }

    /**
     * Updates an existing MenuItem model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        // Load the model
        $model = $this->findModel($id);

        // The view params
        $params = $this->getDefaultViewParams($model);

        if (Yii::$app->request->getIsPost()) {

            $post = Yii::$app->request->post();

            // Ajax request, validate
            if (Yii::$app->request->isAjax) {

                return $this->validateModel($model, $post);

            // Normal request, save models
            } else {
                return $this->saveModel($model, $post);
            }
        }

        return $this->render('update', $params);
    }

    /**
     * Deletes an existing MenuItem model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $name = $model->name;

        try {

            $transaction = Yii::$app->db->beginTransaction();

            // Only Superadmin can delete system pages
            if ($model->type == MenuItem::TYPE_SYSTEM && !Yii::$app->user->can('Superadmin'))
                throw new \yii\base\Exception(Yii::t('app', 'You do not have the right permissions to delete this item'));

            $model->delete();
            $transaction->commit();
        } catch(\yii\base\Exception $e) {
            $transaction->rollBack();
            // Set flash message
            Yii::$app->getSession()->setFlash('menu-item-error', $e->getMessage());

            return $this->redirect(['index']);
        }

        // Set flash message
        Yii::$app->getSession()->setFlash('menu-item', Yii::t('app', '"{item}" has been deleted', ['item' => $name]));

        return $this->redirect(['index']);
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
                throw new \Exception(Yii::t('infoweb/menu', 'Invalid items'));

            // Delete first item because of bug in nestedsortable
            array_shift($post['ids']);

            $positions = array();

            foreach ($post['ids'] as $k => $v)
            {
                $item_id = $v['item_id'];
                $parent_id = $v['parent_id'];

                // Create menu item
                $menu_item = MenuItem::findOne($item_id);
                $menu_item->parent_id = (int) $v['parent_id'];
                $menu_item->level = $v['depth'] - 1;

                if (!isset($positions["{$menu_item->parent_id}-{$menu_item->level}"]))
                    $positions["{$menu_item->parent_id}-{$menu_item->level}"] = 0;

                // Set rest of attributes and save
                $menu_item->position = $positions["{$menu_item->parent_id}-{$menu_item->level}"] + 1;
                $positions["{$menu_item->parent_id}-{$menu_item->level}"]++;

                if (!$menu_item->save()) {
                    throw new \Exception(Yii::t('app', 'Error while saving'));
                }
            }
        } catch (\Exception $e) {
            Yii::error($e->getMessage());
            exit();
        }

        $data['status'] = 1;

        Yii::$app->response->format = 'json';
        return $data;
    }

    /**
     * Set active state
     * @param string $id
     * @return mixed
     */
    public function actionActive()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel(Yii::$app->request->post('id'));
        $model->active = ($model->active == 1) ? 0 : 1;

        $data['status'] = $model->save();
        $data['active'] = $model->active;

        // Update the status of the children
        foreach ($model->children as $child) {
            $child->active = $model->active;
            $child->save();
        }

        return $data;
    }

    /**
     * Returns a page's html anchors
     *
     * @return  json
     */
    public function actionGetPageHtmlAnchors()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $response = [
            'status'    => 0,
            'msg'       => '',
            'anchors'   => ['' => Yii::t('infoweb/menu', 'Choose an anchor')]
        ];

        $page = Page::findOne(Yii::$app->request->get('page'));

        if ($page) {
            $response['anchors'] = array_merge($response['anchors'], $page->htmlAnchors);
        }

        $response['status'] = 1;

        return $response;
    }

    /**
     * Set public state
     * @param string $id
     * @return mixed
     */
    public function actionPublic()
    {
        $model = $this->findModel(Yii::$app->request->post('id'));
        $model->public = ($model->public == 1) ? 0 : 1;

        // Ajax request
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $response = ['status' => 0];

            if ($model->save()) {
                $response['status'] = 1;
                $response['public'] = $model->public;
            }

            return $response;
        // Normal request
        } else {
            return $model->save();
        }
    }

    /**
     * Updates the positions of the provided menu items
     * @return mixed
     */
    public function actionUpdatePositions()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $response = [
            'status' => 0,
            'msg'    => ''
        ];

        try {
            $items = Yii::$app->request->post('items');

            // Wrap the everything in a database transaction
            $transaction = Yii::$app->db->beginTransaction();

            if (!$this->updatePositions($items)) {
                throw new \Exception(Yii::t('app', 'Error while saving'));
            }

            $transaction->commit();
            $response['status'] = 1;
        } catch (\Exception $e) {
            $response['msg'] = $e->getMessage();
        }

        return $response;
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
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist'));
        }
    }

    /**
     * Returns an array of the default params that are passed to a view
     *
     * @param Page $model The model that has to be passed to the view
     * @return array
     */
    protected function getDefaultViewParams($model = null)
    {
        return [
            'model'                   => $model,
            'menu'                    => $this->findActiveMenu(),
            'linkableEntities'        => $this->findLinkableEntities(),
            'entityTypes'             => $this->entityTypes(),
            'allowContentDuplication' => $this->module->allowContentDuplication
        ];
    }

    protected function findActiveMenu()
    {
        // If no valid active menu-id is set, search the first menu and use it's id
        if (in_array(Yii::$app->session->get('menu-items.menu-id'), [0, null])) {
            $menu = Menu::find()->one();
            Yii::$app->session->set('menu-items.menu-id', $menu->id);
        } else {
            $menu = Menu::findone(Yii::$app->session->get('menu-items.menu-id'));
        }

        return $menu;
    }

    /**
     * Returns a combination of default entityTypes and the one's that are set
     * in the controller.
     *
     * @return  array
     */
    protected function entityTypes()
    {
        return ArrayHelper::merge($this->entityTypes, [
            'url'  => Yii::t('app', 'Url'),
            'none' => Yii::t('infoweb/menu', 'Nothing'),
        ]);
    }

    /**
     * Returns all the entities that can be linked to a menu-item
     *
     * @return  array
     */
    protected function findLinkableEntities()
    {
        $linkableEntities = [];

        foreach ($this->module->linkableEntities as $k => $entity) {
            $entityModel = Yii::createObject($k);

            // The entityModel must have the 'getUrl' and 'getAllForDropDownList' methods
            if (method_exists($entityModel, 'getUrl') && method_exists($entityModel, 'getAllForDropDownList')) {
                $linkableEntities[$k] = [
                    'label' => Yii::t($entity['i18nGroup'], $entity['label']),
                    'data'  => $entityModel->getAllForDropDownList()
                ];

                // Add it also to the entityTypes variable of the controller
                $this->entityTypes[$k] = $linkableEntities[$k]['label'];
            }
        }

        return $linkableEntities;
    }

     /**
     * Performs validation on the provided model and $_POST data
     *
     * @param \infoweb\pages\models\Page $model The page model
     * @param array $post The $_POST data
     * @return array
     */
    protected function validateModel($model, $post)
    {
        $languages = Yii::$app->params['languages'];

        // Populate the model with the POST data
        $model->load($post);

        // Parent is root
        if (empty($post[StringHelper::basename(MenuItem::className())]['parent_id'])) {
            $model->parent_id = 0;
            $model->level = 0;
        } else {
            $parent = MenuItem::findOne($post[StringHelper::basename(MenuItem::className())]['parent_id']);
            $model->parent_id = $parent->id;
            $model->level = $parent->level + 1;
        }

        // Create an array of translation models and populate them
        $translationModels = [];
        // Insert
        if ($model->isNewRecord) {
            foreach ($languages as $languageId => $languageName) {
                $translationModels[$languageId] = new MenuItemLang(['language' => $languageId]);
            }
        // Update
        } else {
            $translationModels = ArrayHelper::index($model->getTranslations()->all(), 'language');
        }
        Model::loadMultiple($translationModels, $post);

        // Validate the model and translation
        $response = array_merge(
            ActiveForm::validate($model),
            ActiveForm::validateMultiple($translationModels)
        );

        // Return validation in JSON format
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $response;
    }

    protected function saveModel($model, $post)
    {
        // Wrap everything in a database transaction
        $transaction = Yii::$app->db->beginTransaction();

        // Get the params
        $params = $this->getDefaultViewParams($model);
        $currentParentId = $model->parent_id;

        // Populate the model with the POST data
        $model->load($post);

        // Set level and calculate next position for a new record or when
        // the parent has changed
        if ($model->isNewRecord || (!$model->isNewRecord && $currentParentId != $model->parent_id)) {
            $model->level = ($model->parent_id) ? $model->parent->level + 1 : 0;
            $model->position = $model->nextPosition();
        }

        // Validate the main model
        if (!$model->validate()) {
            return $this->render($this->action->id, $params);
        }

        // Add the translations
        foreach (Yii::$app->request->post(StringHelper::basename(MenuItemLang::className()), []) as $language => $data) {
            foreach ($data as $attribute => $translation) {
                $model->translate($language)->$attribute = $translation;
            }
        }

        // Save the main model
        if (!$model->save()) {
            return $this->render($this->action->id, $params);
        }

        $transaction->commit();

        // Set flash message
        if ($this->action->id == 'create') {
            Yii::$app->getSession()->setFlash('page', Yii::t('app', '"{item}" has been created', ['item' => $model->name]));
        } else {
            Yii::$app->getSession()->setFlash('page', Yii::t('app', '"{item}" has been updated', ['item' => $model->name]));
        }

        // Take appropriate action based on the pushed button
        if (isset($post['save-close'])) {
            return $this->redirect(['index']);
        } elseif (isset($post['save-add'])) {
            return $this->redirect(['create']);
        } else {
            return $this->redirect(['update', 'id' => $model->id]);
        }
    }

    /**
     * Updates the positions of the provided menu items
     *
     * @param   array                           $items      The menu items
     * @param   infoweb\menu\models\MenuItem
     * @return  boolean
     */
    protected function updatePositions($items = [], $parent = null)
    {
        // Determine the parentId and level
        $parentId = ($parent !== null) ? $parent->id : 0;
        $level = ($parent !== null) ? $parent->level + 1 : 0;

        foreach ($items as $k => $item) {
            // Update the menu item
            $menuItem = MenuItem::findOne($item['id']);
            $menuItem->parent_id = $parentId;
            $menuItem->level = $level;
            $menuItem->position = $k + 1;

            if (!$menuItem->save()) {
                throw new \Exception("Error while saving menuItem #{$menuItem->id}");
            }

            // Update the position of the item's children
            if (isset($item['children'])) {
                $this->updatePositions($item['children'], $menuItem);
            }
        }

        return true;
    }
}
