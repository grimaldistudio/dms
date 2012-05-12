<?php
class RoleController extends SecureController{
    
    private $_model = null;
    
    public function actionCreate()
    {
        $model = new Role();
        $model->setScenario('create');
        if(isset($_POST['Role']))
        {
            $model->setAttributes($_POST['Role']);
            if($model->save())
            {
                if(Yii::app()->request->isAjaxRequest)
                {
                    $this->ajaxSuccess('Ruolo creato con successo');
                }
                else
                {
                    Yii::app()->user->setFlash('success', 'Ruolo creato con successo');
                    $this->redirect(array('/role/view', 'id'=>$model->id));
                }
            }
            elseif(Yii::app()->request->isAjaxRequest)
            {
                $this->ajaxError($model->getErrors());
            }
        }
        
        $this->render('create', array('model'=>$model));
    }
    
    public function actionDelete()
    {
        $model = $this->loadModel();
        if($model->delete())
            echo $this->ajaxSuccess ("Ruolo cancellato");
        else
            throw new CHttpException(500, "Impossibile cancellare il ruolo");
    }
    
    public function actionUpdate()
    {
        $model = $this->loadModel();
        $model->setScenario('update');
        $model->loadRightIds(); // load right ids
        
        if(isset($_POST['Role']))
        {
            $model->setAttributes($_POST['Role']);
            if($model->save())
            {
                if(Yii::app()->request->isAjaxRequest)
                {
                    $this->ajaxSuccess('Ruolo modificato con successo');
                }
                else
                {
                    Yii::app()->user->setFlash('success', 'Ruolo modificato con successo');
                    $this->redirect(array('/role/view', 'id'=>$model->id));                                    
                }

            }
            elseif(Yii::app()->request->isAjaxRequest)
            {
                $this->ajaxError($model->getErrors());
            }
        }

        if(Yii::app()->request->isAjaxRequest)
        {
            $script_map = array(
                'bootstrap.min.css'=>false,
                'jquery-ui.latest.css'=>false,
                'ui.multiselect.js'=>false,
                'jquery.latest.js'=>false,
                'jquery.yiiactiveform.js'=>false,
                'ui.multiselect.css'=>false,
                'bootstrap-transition.js'=>false,
                'bootstrap-button.js'=>false,
                'bootstrap-tooltip.js'=>false,
                'bootstrap-popover.js'=>false,
                'bootstrap-alert.js'=>false,
                'jquery-ui.min.latest.js'=>false,
                'dms.js'=>false
            );
            Yii::app()->clientScript->scriptMap = $script_map;
            $this->renderPartial('//role/_ajaxform', array('model'=>$model), false, true);
        }
        else
            $this->render('//role/update', array('model'=>$model));
    }
    
    public function actionIndex()
    {
        $model = new Role('search');
        $model->unsetAttributes();
        if(isset($_GET['Role']))
            $model->attributes = $_GET['Role'];

        $params =array(
            'model'=>$model,
        );

        if(!isset($_GET['ajax']))
            $this->render('list', $params);
        else
            $this->renderPartial('list', $params);        
    }
    
    public function actionView()
    {
        $model = $this->loadModel();
        if(Yii::app()->request->isAjaxRequest)
        {
            $script_map = array(
                'bootstrap.min.css'=>false,
                'jquery.latest.js'=>false,
                'bootstrap-transition.js'=>false,
                'bootstrap-button.js'=>false,
                'bootstrap-tooltip.js'=>false,
                'bootstrap-popover.js'=>false,
                'bootstrap-alert.js'=>false,
                'dms.js' => false
            );
            Yii::app()->clientScript->scriptMap = $script_map;
            $this->renderPartial('view', 
                            array(
                                'model'=>$model,
                                'isAjax'=>true
                            ),false,true);
            Yii::app()->end();			
        }
        else
            $this->render('view', array('model'=>$model));	        
    }
    
    protected function loadModel()
    {
        if($this->_model===null)
        {
            if(isset($_GET['id']))
            {
                $this->_model=Role::model()->findByPk($_GET['id']);
            }

            if($this->_model===null)
                throw new CHttpException(404,'La pagina richiesta non esiste.');

        }

        return $this->_model;
    }
    
}