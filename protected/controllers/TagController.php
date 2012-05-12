<?php
class TagController extends SecureController{

    private $_model = null;
    
    public function actionIndex()
    {
        $model = new Tag('search');
        $model->unsetAttributes();
        if(isset($_GET['Tag']))
            $model->attributes = $_GET['Tag'];

        $params =array(
            'model'=>$model,
        );

        if(!isset($_GET['ajax']))
            $this->render('list', $params);
        else
            $this->renderPartial('list', $params);          
    }
    
    public function actionCreate()
    {
        $model = new Tag();
        $model->setScenario('create');
        if(isset($_POST['Tag']))
        {
            $model->setAttributes($_POST['Tag']);
            if($model->save())
            {
                if(Yii::app()->request->isAjaxRequest)
                {
                    $this->ajaxSuccess('Tag creato con successo');
                }
                else
                {
                    Yii::app()->user->setFlash('success', 'Tag creato con successo');
                    $this->redirect(array('/tag/index', 'id'=>$model->id));
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
        
        if(isset($_POST['Tag']))
        {
            $model->setAttributes($_POST['Tag']);
            if($model->save())
            {
                if(Yii::app()->request->isAjaxRequest)
                {
                    $this->ajaxSuccess('Tag modificato con successo');
                }
                else
                {
                    Yii::app()->user->setFlash('success', 'Tag modificato con successo');
                    $this->redirect(array('/tag/index'));                                    
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
            $this->renderPartial('//tag/_ajaxform', array('model'=>$model), false, true);
        }
        else
            $this->render('//tag/update', array('model'=>$model));        
    }

    public function actionDelete()
    {
        $model = $this->loadModel();
        if($model->delete())
            echo $this->ajaxSuccess ("Tag cancellato");
        else
            throw new CHttpException(500, "Impossibile cancellare il tag");        
    }
    
    public function actionAutocomplete($term = '')
    {
        $tag = new Tag('search');
        $tags = $tag->autocomplete($term);
        echo CJSON::encode($tags);
        Yii::app()->end();
    }
    
    public function actionAutocompletetoken($term = '')
    {
        $tag = new Tag('search');
        $tags = $tag->autocompletetoken($term);
        echo CJSON::encode($tags);
        Yii::app()->end();
    }
    
    protected function loadModel()
    {
        if($this->_model===null)
        {
            if(isset($_GET['id']))
            {
                $this->_model=Tag::model()->findByPk($_GET['id']);
            }

            if($this->_model===null)
                throw new CHttpException(404,'La pagina richiesta non esiste.');

        }

        return $this->_model;
    }
}

?>
