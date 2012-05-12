<?php
class RightController extends SecureController{
    
    private $_model = null;
    
    public function actionCreate()
    {
        $model = new Right();
        $model->setScenario('create');
        if(isset($_POST['Right']))
        {
            $model->setAttributes($_POST['Right']);
            if($model->save())
            {
                if(Yii::app()->request->isAjaxRequest)
                {
                    $this->ajaxSuccess('Permesso creato con successo');
                }
                else
                {
                    Yii::app()->user->setFlash('success', 'Permesso creato con successo');
                    $this->redirect(array('/right/view', 'id'=>$model->id));
                }
            }
            elseif(Yii::app()->request->isAjaxRequest)
            {
                $this->ajaxError($model->getErrors());
            }
        }
        $this->render('create', array('model'=>$model));
    }
    
    public function actionUpdate()
    {
        $model = $this->loadModel();
        $model->setScenario('update');
        $model->loadRoleIds();
        if(isset($_POST['Right']))
        {
            $model->setAttributes($_POST['Right']);
            if($model->save())
            {
                if(Yii::app()->request->isAjaxRequest)
                {
                    $this->ajaxSuccess('Permesso modificato con successo');
                }
                else
                {
                    Yii::app()->user->setFlash('success', 'Permesso modificato con successo');
                    $this->redirect(array('/right/view', 'id'=>$model->id));                                    
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
            $this->renderPartial('//right/_ajaxform', array('model'=>$model), false, true);
        }
        else
            $this->render('//right/update', array('model'=>$model));
    }

    public function actionDelete()
    {
        $model = $this->loadModel();
        if($model->delete())
            echo $this->ajaxSuccess ("Permesso cancellato");
        else
            throw new CHttpException(500, "Impossibile cancellare il permesso");
    }
    
    public function actionIndex()
    {
        $model = new Right('search');
        $model->unsetAttributes();
        if(isset($_GET['Right']))
            $model->attributes = $_GET['Right'];

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
                'dms.js'=>false
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
                $this->_model=Right::model()->findByPk($_GET['id']);
            }

            if($this->_model===null)
                throw new CHttpException(404,'La pagina richiesta non esiste.');

        }

        return $this->_model;
    }
}