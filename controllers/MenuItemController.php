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
     * @var boolean whether to enable CSRF validation for the actions in this controller.
     * CSRF validation is enabled only when both this property and [[Request::enableCsrfValidation]] are true.
     */
    public $enableCsrfValidation = false;
    
    /**
     * The entity types that can be linked to a menu-item
     * @var array
     */
    protected $entityTypes = [];
    
    /**
     * Returns a combination of default entityTypes and the one's that are set
     * in the controller.
     * 
     * @return  array
     */
    protected function entityTypes()
    {
        return ArrayHelper::merge([
            'page'          => Yii::t('infoweb/pages', 'Page'),
            'menu-item'     => Yii::t('infoweb/menu', 'Menu item'),
            'url'           => Yii::t('app', 'Url')
        ], $this->entityTypes);
    }

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
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
        // Store the active menu-id in a session var if it is provided through the url
        if (Yii::$app->request->get('menu-id') != null)
            Yii::$app->session->set('menu-items.menu-id', Yii::$app->request->get('menu-id'));

        $searchModel = new MenuItemSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $menu = $this->findActiveMenu();

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'menu' => $menu,
            'maxLevel' => json_encode($menu->max_level),
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
        $languages = Yii::$app->params['languages'];
        $menu = $this->findActiveMenu();
        $levelSelect = Menu::level_select(['menu-id' => $menu->id]);
        $pages = Page::getAllForDropDownList();
        $linkableEntities = $this->findLinkableEntities();
        $entityTypes = $this->entityTypes();        

        // Initialize the menu-item with default values
        $model = new MenuItem([
            'menu_id'   => $menu->id,
            'active'    => 1,
            'public'    => (int) $this->module->defaultPublicVisibility,
            'type'      => MenuItem::TYPE_USER_DEFINED,
        ]);
        
        if (Yii::$app->request->getIsPost()) {
            
            $post = Yii::$app->request->post();
            
            // Ajax request, validate the models
            if (Yii::$app->request->isAjax) {
                               
                // Populate the model with the POST data
                $model->load($post);
                
                // Set model level
                // Parent is root
                if (empty($post['MenuItem']['parent_id'])) {
                    $model->level = 0;
                } else {
                    // Load parent
                    $parent = MenuItem::findOne($post['MenuItem']['parent_id']);
                    $model->level       = $parent->level + 1;
                }
                
                // Create an array of translation models
                $translationModels = [];
                
                foreach ($languages as $languageId => $languageName) {
                    $translationModels[$languageId] = new MenuItemLang(['language' => $languageId]);
                }
                
                // Populate the translation models
                Model::loadMultiple($translationModels, $post);

                // Validate the model and translation models
                $response = array_merge(ActiveForm::validate($model), ActiveForm::validateMultiple($translationModels));
                
                // Return validation in JSON format
                Yii::$app->response->format = Response::FORMAT_JSON;
                return $response;
            
            // Normal request, save models
            } else {
                // Wrap the everything in a database transaction
                $transaction = Yii::$app->db->beginTransaction();                
                
                // Parent is root
                if (empty($post['MenuItem']['parent_id'])) {
                    // Set parent and level
                    $model->parent_id = 0;
                    $model->level = 0;
                } else {
                    // Load parent
                    $parent = MenuItem::findOne($post['MenuItem']['parent_id']);
    
                    // Set parent and level
                    $model->parent_id   = $parent->id;
                    $model->level       = $parent->level + 1;
                }
    
                // Set rest of attributes and save
                $model->position = $model->nextPosition();
                $model->entity_id = (isset($post['MenuItem']['entity_id'])) ? $post['MenuItem']['entity_id'] : 0;
                $model->active = 1;
                
                // Save the main model
                if (!$model->load($post) || !$model->save()) {
                    return $this->render('create', [
                        'model'             => $model,
                        'levelSelect'       => $levelSelect,
                        'menu'              => $menu,
                        'pages'             => $pages,
                        'linkableEntities'  => $linkableEntities,
                        'entityTypes'       => $entityTypes
                    ]);
                }
                
                // Save the translations
                foreach ($languages as $languageId => $languageName) {
                    
                    $data = $post['MenuItemLang'][$languageId];
                    
                    // Set the translation language and attributes                    
                    $model->language    = $languageId;
                    $model->name        = $data['name'];
                    $model->params      = $data['params'];
                    
                    if (!$model->saveTranslation()) {
                        return $this->render('create', [
                            'model'             => $model,
                            'levelSelect'       => $levelSelect,
                            'menu'              => $menu,
                            'pages'             => $pages,
                            'linkableEntities'  => $linkableEntities,
                            'entityTypes'       => $entityTypes,
                        ]);    
                    }                      
                }
                
                $transaction->commit();
                
                // Switch back to the main language
                $model->language = Yii::$app->language;
                
                // Set flash message
                Yii::$app->getSession()->setFlash('menu-item', Yii::t('app', '"{item}" has been created', ['item' => $model->name]));
              
                // Take appropriate action based on the pushed button
                if (isset($post['close'])) {
                    return $this->redirect(['index']);
                } elseif (isset($post['new'])) {
                    return $this->redirect(['create']);
                } else {
                    return $this->redirect(['update', 'id' => $model->id]);
                }    
            }    
        }

        return $this->render('create', [
            'model'             => $model,
            'levelSelect'       => $levelSelect,
            'menu'              => $menu,
            'pages'             => $pages,
            'linkableEntities'  => $linkableEntities,
            'entityTypes'       => $entityTypes
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
        $languages = Yii::$app->params['languages'];        
        $model = $this->findModel($id);
        $menu = $this->findActiveMenu();
        $levelSelect = Menu::level_select([
            'menu-id'   => Yii::$app->session->get('menu-items.menu-id'),
            'selected'  => $model->parent_id
        ]);
        $pages = Page::getAllForDropDownList();
        $linkableEntities = $this->findLinkableEntities();
        $entityTypes = $this->entityTypes();
        
        if (Yii::$app->request->getIsPost()) {
            
            $post = Yii::$app->request->post();
            
            // Ajax request, validate the models
            if (Yii::$app->request->isAjax) {
                               
                // Populate the model with the POST data
                $model->load($post);
                
                // Set model level
                // Parent is root
                if (empty($post['MenuItem']['parent_id'])) {
                    $model->level = 0;
                } else {
                    // Load parent
                    $parent = MenuItem::findOne($post['MenuItem']['parent_id']);
                    $model->level       = $parent->level + 1;
                }
                
                // Create an array of translation models
                $translationModels = [];
                
                foreach ($languages as $languageId => $languageName) {
                    $translationModels[$languageId] = new MenuItemLang(['language' => $languageId]);
                }
                
                // Populate the translation models
                Model::loadMultiple($translationModels, $post);

                // Validate the model and translation models
                $response = array_merge(ActiveForm::validate($model), ActiveForm::validateMultiple($translationModels));
                
                // Return validation in JSON format
                Yii::$app->response->format = Response::FORMAT_JSON;
                return $response;
            
            // Normal request, save models
            } else {

                // Wrap the everything in a database transaction
                $transaction = Yii::$app->db->beginTransaction(); 
                                
                $currentParentId = $model->parent_id;
                
                // Check if the parent has changed
                if ($currentParentId != $post['MenuItem']['parent_id'])
                {
                    // Change parent, level and position
                    // Parent is root
                    if (empty($post['MenuItem']['parent_id'])) {
                        // Set parent and level
                        $model->parent_id = 0;
                        $model->level = 0;
                    } else {
                        // Load parent
                        $parent = MenuItem::findOne($post['MenuItem']['parent_id']);
        
                        // Set parent and level
                        $model->parent_id   = $parent->id;
                        $model->level       = $parent->level + 1;
                    }
        
                    // Set rest of attributes and save
                    $model->position = $model->nextPosition();
                }             

                $model->entity_id =  (isset($post['MenuItem']['entity_id'])) ? $post['MenuItem']['entity_id'] : 0;
                
                // If the item is not linked to a page, always reset the anchor value
                if ($model->entity != MenuItem::ENTITY_PAGE) {
                    $model->anchor = '';
                }

                // Save the main model
                if (!$model->load($post) || !$model->save()) {
                    return $this->render('update', [
                        'model'             => $model,
                        'levelSelect'       => $levelSelect,
                        'menu'              => $menu,
                        'pages'             => $pages,
                        'linkableEntities'  => $linkableEntities,
                        'entityTypes'       => $entityTypes
                    ]);
                }
                
                // Save the translations
                foreach ($languages as $languageId => $languageName) {
                    
                    $data = $post['MenuItemLang'][$languageId];
                    
                    // Set the translation language and attributes                    
                    $model->language    = $languageId;
                    $model->name        = $data['name'];
                    $model->params      = $data['params'];
                    
                    if (!$model->saveTranslation()) {
                        return $this->render('update', [
                            'model'             => $model,
                            'levelSelect'       => $levelSelect,
                            'menu'              => $menu,
                            'pages'             => $pages,
                            'linkableEntities'  => $linkableEntities,
                            'entityTypes'       => $entityTypes
                        ]);    
                    }                      
                }
                
                $transaction->commit();
                
                // Switch back to the main language
                $model->language = Yii::$app->language;
                
                // Set flash message
                Yii::$app->getSession()->setFlash('menu-item', Yii::t('app', '"{item}" has been updated', ['item' => $model->name]));
              
                // Take appropriate action based on the pushed button
                if (isset($post['close'])) {
                    return $this->redirect(['index']);
                } elseif (isset($post['new'])) {
                    return $this->redirect(['create']);
                } else {
                    return $this->redirect(['update', 'id' => $model->id]);
                }    
            }    
        }
        
        return $this->render('update', [
            'model'             => $model,
            'levelSelect'       => $levelSelect,
            'menu'              => $menu,
            'pages'             => $pages,
            'linkableEntities'  => $linkableEntities,
            'entityTypes'       => $entityTypes
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
        $model = $this->findModel($id);

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
        Yii::$app->getSession()->setFlash('menu-item', Yii::t('app', '"{item}" has been deleted', ['item' => $model->name]));

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
     * Returns all the entities that can be linked to a menu-item
     * 
     * @return  array
     */
    protected function findLinkableEntities()
    {
        $linkableEntities = [];
        
        foreach ($this->module->linkableEntities as $k => $entity) {
            $entityModel = Yii::createObject($entity['class']);
            
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
}
