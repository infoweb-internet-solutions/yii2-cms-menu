<?php

namespace infoweb\menu\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\db\Query;
use yii\web\session;
use yii\web\AssetManager;
use yii\web\Response;
use yii\widgets\ActiveForm;
use yii\base\Model;
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
        
        // If no valid active menu-id is set, search the first menu and use it's id
        if (in_array(Yii::$app->session->get('menu-items.menu-id'), [0, null])) {
            $menu = Menu::findone();
            
            Yii::$app->session->set('menu-items.menu-id', $menu->id); 
        } else {
            $menu = Menu::findone(Yii::$app->session->get('menu-items.menu-id'));    
        }
        
        $searchModel = new MenuItemSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

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
        
        // Get the active menu
        $menu = Menu::findone(Yii::$app->session->get('menu-items.menu-id'));
        
        // Initialize the menu-item with default values
        $model = new MenuItem(['menu_id' => $menu->id, 'active' => 1]);
        
        if (Yii::$app->request->getIsPost()) {
            
            $post = Yii::$app->request->post();
            
            // Ajax request, validate the models
            if (Yii::$app->request->isAjax) {
                               
                // Populate the model with the POST data
                $model->load($post);
                
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
                $model->entity_id = $post['MenuItem']['entity_id'];
                $model->active = 1;
                
                // Save the main model
                if (!$model->load($post) || !$model->save()) {
                    return $this->render('create', [
                        'model' => $model
                    ]);
                }
                
                // Save the translations
                foreach ($languages as $languageId => $languageName) {
                    
                    $data = $post['MenuItemLang'][$languageId];
                    
                    // Set the translation language and attributes                    
                    $model->language    = $languageId;
                    $model->name        = $data['name'];
                    
                    if (!$model->saveTranslation()) {
                        return $this->render('create', [
                            'model' => $model
                        ]);    
                    }                      
                }
                
                $transaction->commit();
                
                // Switch back to the main language
                $model->language = Yii::$app->language;
                
                // Set flash message
                Yii::$app->getSession()->setFlash('menu-item', Yii::t('app', '{item} has been created', ['item' => $model->name]));
              
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

        // Get all pages
        $pages = (new Query())
                    ->select('page.id, page_lang.name')
                    ->from(['page' => 'pages'])
                    ->innerJoin(['page_lang' => 'pages_lang'], "page.id = page_lang.page_id AND page_lang.language = '".Yii::$app->language."'")
                    ->orderBy(['page_lang.name' => SORT_ASC])
                    ->all();
        
        return $this->render('create', [
            'model' => $model,
            'levelSelect' => $menu->level_select(['menu-id' => Yii::$app->request->get('menu-id')]),
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
        $languages = Yii::$app->params['languages'];
        
        // Get the active menu
        $menu = Menu::findone(Yii::$app->session->get('menu-items.menu-id'));
        
        $model = $this->findModel($id);
        
        if (Yii::$app->request->getIsPost()) {
            
            $post = Yii::$app->request->post();
            
            // Ajax request, validate the models
            if (Yii::$app->request->isAjax) {
                               
                // Populate the model with the POST data
                $model->load($post);
                
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
                
                $model->entity_id = $post['MenuItem']['entity_id'];
                
                // Save the main model
                if (!$model->load($post) || !$model->save()) {
                    return $this->render('update', [
                        'model' => $model
                    ]);
                }
                
                // Save the translations
                foreach ($languages as $languageId => $languageName) {
                    
                    $data = $post['MenuItemLang'][$languageId];
                    
                    // Set the translation language and attributes                    
                    $model->language    = $languageId;
                    $model->name        = $data['name'];
                    
                    if (!$model->saveTranslation()) {
                        return $this->render('update', [
                            'model' => $model
                        ]);    
                    }                      
                }
                
                $transaction->commit();
                
                // Switch back to the main language
                $model->language = Yii::$app->language;
                
                // Set flash message
                Yii::$app->getSession()->setFlash('menu-item', Yii::t('app', '{item} has been updated', ['item' => $model->name]));
              
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

        // Get all pages
        $pages = (new Query())
                    ->select('page.id, page_lang.name')
                    ->from(['page' => 'pages'])
                    ->innerJoin(['page_lang' => 'pages_lang'], "page.id = page_lang.page_id AND page_lang.language = '".Yii::$app->language."'")
                    ->orderBy(['page_lang.name' => SORT_ASC])
                    ->all();
        
        return $this->render('update', [
            'model' => $model,
            'levelSelect' => $menu->level_select(['menu-id' => Yii::$app->request->get('menu-id')]),
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
        $model = $this->findModel($id);
        $model->delete();
        
        // Set flash message
        $model->language = Yii::$app->language;
        Yii::$app->getSession()->setFlash('menu-item', Yii::t('app', '{item} has been deleted', ['item' => $model->name]));

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
                throw new \Exception('Ongeldige menu items');

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
                    throw new \Exception("Fout bij het opslaan");
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
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
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
