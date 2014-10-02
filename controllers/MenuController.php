<?php

namespace infoweb\menu\controllers;

use Yii;
use infoweb\menu\models\Menu;
use infoweb\menu\models\MenuSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * MenuController implements the CRUD actions for Menu model.
 */
class MenuController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all Menu models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new MenuSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        // Create button settings
        $createButton = Html::a(Yii::t('app', 'Create {modelClass}', [
            'modelClass' => 'Menu',
        ]), ['create'], ['class' => 'btn btn-success']);
        
        // Gridview settings
        $gridView = [
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'layout' => "{items}{pager}",
            'columns' => [
                [
                    'class' => '\kartik\grid\DataColumn',
                    'attribute' => 'name',
                    'format' => 'raw',
                    'value'=>function ($model) {
                        return Html::a(Html::encode($model->name), Url::toRoute(['update', 'id' => $model->id]), [
                            'title' => Yii::t('app', 'Update'),
                            'data-pjax' => '0',
                            'data-toggle' => 'tooltip',
                            'class' => 'edit-model',
                        ]);
                    },
                ],
                [
                    'class' => 'kartik\grid\ActionColumn',
                    'template' => (Yii::$app->user->can('superAdmin')) ? '{update} {delete} {menu-item}' : '{update} {menu-item}',
                    'buttons' => [
                        'menu-item' => function ($url, $model) {
                            return Html::a('<span class="glyphicon glyphicon-list-alt"></span>', $url, [
                                'title' => Yii::t('app', 'Menu Items'),
                                'data-pjax' => '0',
                                'data-toggle' => 'tooltip',
                                //'data-placement' => 'left',
                            ]);
                        },
                    ],
                    'updateOptions'=>['title'=> 'Update', 'data-toggle'=>'tooltip'],
                    'deleteOptions'=>['title'=> 'Delete', 'data-toggle'=>'tooltip'],
                    'urlCreator' => function($action, $model, $key, $index) {
        
                        if ($action == 'menu-item')
                        {
                            $params = is_array($key) ? $key : ['menu-id' => (int) $key];
                            $params[0] = $action . '/index';
                        } else {
                            $params = is_array($key) ? $key : ['id' => (int) $key];
                            $params[0] = 'menu' . '/' . $action;
                        }
        
                        return Url::toRoute($params);
                    },
                    'width' => '100px',
                ]
            ],
            'responsive' => true,
            'floatHeader' => true,
            'floatHeaderOptions' => ['scrollingTop' => 88],
            'hover' => true
        ];
        
        return $this->render('index', [
            'gridView' => $gridView,
            'createButton' => $createButton,
        ]);
    }

    /**
     * Displays a single Menu model.
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
     * Creates a new Menu model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Menu();
        $model->loadDefaultValues();

        $post = Yii::$app->request->post();

        if ($model->load($post) && $model->save()) {

            if (isset($post['close'])) {
                return $this->redirect(['index']);
            } elseif (isset($post['new'])) {
                return $this->redirect(['create']);
            } else {
                return $this->redirect(['update', 'id' => $model->id]);
            }
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Menu model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $post = Yii::$app->request->post();

        if ($model->load($post) && $model->save()) {

            if (isset($post['close'])) {
                return $this->redirect(['index']);
            } elseif (isset($post['new'])) {
                return $this->redirect(['create']);
            } else {
                return $this->redirect(['update', 'id' => $model->id]);
            }

        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Menu model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    public function actionMenuItems()
    {
        return $this->redirect(['menu-item/index']);
    }

    /**
     * Finds the Menu model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Menu the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Menu::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
