<?php
class SenderController extends SecureController{

    private $_model = null;
    
    public function actionIndex()
    {
        $model = new Sender('search');
        $model->unsetAttributes();
        if(isset($_GET['Sender']))
            $model->attributes = $_GET['Sender'];

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
        $model = new Sender();
        $model->setScenario('create');
        if(isset($_POST['Sender']))
        {
            $model->setAttributes($_POST['Sender']);
            if($model->save())
            {
                if(Yii::app()->request->isAjaxRequest)
                {
                    $ret = array();
                    $ret['success'] = 1;
                    $ret['id'] = $model->id;
                    $ret['label'] = $model->name;
                    echo CJSON::encode($ret);
                    Yii::app()->end();   
//                    $this->ajaxSuccess('Mittent aggiunto alla rubrica');
                }
                else
                {
                    Yii::app()->user->setFlash('success', 'Mittente aggiunto alla rubrica');
                    $this->redirect(array('/sender/index'));
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
        if($model->disable())
        {
            if(Yii::app()->request->isAjaxRequest)
                $this->ajaxSuccess ("Mittente disabilitato. Non sarà più selezionabile per un documento.");
            else
            {
                Yii::app()->user->setFlash('success', 'Mittente disabilitato. Non sarà più selezionabile per un documento.');
                $this->redirect(array('/sender/index'));
            }
        }
        else
        {
            if(Yii::app()->request->isAjaxRequest)
                throw new CHttpException(500, "Impossibile disabilitare il mittente");
            else
            {
                Yii::app()->user->setFlash('error', 'Impossibile disabilitare il mittente');
                $this->redirect(array('/sender/index/'));
            }
        }        
    }

        public function actionEnable()
    {
        $model = $this->loadModel();
        if($model->enable())
        {
            if(Yii::app()->request->isAjaxRequest)
                $this->ajaxSuccess ("Mittente riabilitato. Da ora è possibilie selezionarlo per un documento.");
            else
            {
                Yii::app()->user->setFlash('success', 'Mittente riabilitato. Da ora è possibilie selezionarlo per un documento.');
                $this->redirect(array('/sender/index/'));
            }
        }
        else
        {
            if(Yii::app()->request->isAjaxRequest)
                throw new CHttpException(500, "Impossibile riabilitare il mittente");
            else
            {
                Yii::app()->user->setFlash('error', 'Impossibile riabilitare il mittente');
                $this->redirect(array('/sender/index/'));
            }
        }
    }
    
    public function actionUpdate()
    {
        $model = $this->loadModel();
        $model->setScenario('update');
        
        if(isset($_POST['Sender']))
        {
            $model->setAttributes($_POST['Sender']);
            if($model->save())
            {
                if(Yii::app()->request->isAjaxRequest)
                {
                    $this->ajaxSuccess('Mittente modificato con successo');
                }
                else
                {
                    Yii::app()->user->setFlash('success', 'Mittente modificato con successo');
                    $this->redirect(array('/sender/index'));                                    
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
            $this->renderPartial('//sender/_ajaxform', array('model'=>$model), false, true);
        }
        else
            $this->render('//sender/update', array('model'=>$model));        
    }
    
    public function actionView()
    {
        $model = $this->loadModel();
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
            $this->renderPartial('view', 
                            array(
                                'model'=>$model,
                                'isAjax'=>true
                            ),false,true);
            Yii::app()->end();			
        }
        else
        {
            $this->render('view', array('model'=>$model));            
        }
    }
    
    public function actionAutocomplete($term = '')
    {
        $sender = new Sender('search');
        $senders = $sender->autocomplete($term);
        echo CJSON::encode($senders);
        Yii::app()->end();
    }
    
    protected function loadModel()
    {
        if($this->_model===null)
        {
            if(isset($_GET['id']))
            {
                $this->_model=Sender::model()->findByPk($_GET['id']);
            }

            if($this->_model===null)
                throw new CHttpException(404,'La pagina richiesta non esiste.');

        }

        return $this->_model;
    }
}

?>
