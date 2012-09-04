<?php
class UserController extends SecureController{
    
    private $_model = null;
    
    public function actionCreate()
    {
        $model = new User();
        $model->setScenario('create');
        if(isset($_POST['User']))
        {
            $model->setAttributes($_POST['User']);
            if($model->createAccount())
            {
                if(Yii::app()->request->isAjaxRequest)
                {
                    $this->ajaxSuccess('Utente creato con successo');
                }
                else
                {
                    Yii::app()->user->setFlash('success', 'Utente creato con successo');
                    $this->redirect(array('/user/view', 'id'=>$model->id));
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
        $model->scenario = 'view';

        if($model->is_admin && !Yii::app()->user->isSuperadmin())
        {
            throw new CHttpException(403, 'Azione non consentita');
        }

        if($model->disable())
        {
            if(Yii::app()->request->isAjaxRequest)
                $this->ajaxSuccess ("Utente disabilitato");
            else
            {
                Yii::app()->user->setFlash('success', 'Utente disabilitato');
                $this->redirect(array('/user/view/', 'id'=>$model->id));
            }
        }
        else
        {
            if(Yii::app()->request->isAjaxRequest)
                throw new CHttpException(500, "Impossibile disabilitare l'utente");
            else
            {
                Yii::app()->user->setFlash('error', 'Impossibile disabilitare l\'utente');
                $this->redirect(array('/user/view/', 'id'=>$model->id));
            }
        }
    }
    
    public function actionEnable()
    {
        $model = $this->loadModel();
        $model->scenario = 'view';

        if($model->is_admin && !Yii::app()->user->isSuperadmin())
        {
            throw new CHttpException(403, 'Azione non consentita');
        }
        
        if($model->enable())
        {
            if(Yii::app()->request->isAjaxRequest)
                $this->ajaxSuccess ("Utente riabilitato");
            else
            {
                Yii::app()->user->setFlash('success', 'Utente riabilitato');
                $this->redirect(array('/user/view/', 'id'=>$model->id));
            }
        }
        else
        {
            if(Yii::app()->request->isAjaxRequest)
                throw new CHttpException(500, "Impossibile riabilitare l'utente");
            else
            {
                Yii::app()->user->setFlash('error', 'Impossibile riabilitare l\'utente');
                $this->redirect(array('/user/view/', 'id'=>$model->id));
            }
        }
    }
    
    public function actionUpdate()
    {
        $model = $this->loadModel();
        $model->setScenario('update');

        if($model->is_admin && !Yii::app()->user->isSuperadmin())
        {
            throw new CHttpException(403, 'Azione non consentita');
        }

        $model->loadGroupIds();
        $model->loadRoleIds();
        
        if(isset($_POST['User']))
        {
            $model->setAttributes($_POST['User']);
            if($model->save())
            {
                if(Yii::app()->request->isAjaxRequest)
                {
                    $this->ajaxSuccess('Utente modificato con successo');
                }
                else
                {
                    Yii::app()->user->setFlash('success', 'Utente modificato con successo');
                    $this->redirect(array('/user/view', 'id'=>$model->id));                                    
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
            $this->renderPartial('//user/_ajaxform', array('model'=>$model), false, true);
        }
        else
            $this->render('//user/update', array('model'=>$model));
    }
    
    public function actionIndex()
    {
        $model = new User('search');
        $model->unsetAttributes();
        if(isset($_GET['User']))
            $model->attributes = $_GET['User'];

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
    
    public function actionAutocomplete($q)
    {
        $sql = "SELECT id, firstname, lastname, email FROM users WHERE is_admin=0 AND (firstname LIKE :q OR lastname LIKE :q OR email LIKE :q) ";
        $cmd = Yii::app()->db->createCommand($sql);
        $rows = $cmd->queryAll(true, array(':q'=>'%'.$q.'%'));
        $results = array();
        foreach($rows as $row)
        {
            $result = array();
            $result['id'] = $row['id'];
            $result['name'] = $row['firstname'].' '.$row['lastname']. ' ('.$row['email'].')';
            $results[] = $result;
        }
        echo CJSON::encode($results);
        Yii::app()->end();
    }
   
    public function actionAutocompleter($term)
    {
        $sql = "SELECT id, firstname, lastname, email FROM users WHERE is_admin=0 AND (firstname LIKE :q OR lastname LIKE :q OR email LIKE :q) ";
        $cmd = Yii::app()->db->createCommand($sql);
        $rows = $cmd->queryAll(true, array(':q'=>'%'.$term.'%'));
        $results = array();
        foreach($rows as $row)
        {
            $result = array();
            $result['id'] = $row['id'];
            $result['label'] = $row['firstname'].' '.$row['lastname']. ' ('.$row['email'].')';
            $results[] = $result;
        }
        echo CJSON::encode($results);
        Yii::app()->end();
    }
    
    protected function loadModel()
    {
        if($this->_model===null)
        {
            if(isset($_GET['id']))
            {
                $this->_model=User::model()->findByPk($_GET['id']);
            }

            if($this->_model===null)
                throw new CHttpException(404,'La pagina richiesta non esiste.');

        }

        return $this->_model;
    }
}