<?php
class GroupController extends SecureController{
    
    private $_model = null;
    
    public function actionCreate()
    {
        $model = new Group();
        $model->setScenario('create');
        if(isset($_POST['Group']))
        {
            $model->setAttributes($_POST['Group']);
            if($model->createNew())
            {
                if(Yii::app()->request->isAjaxRequest)
                {
                    $this->ajaxSuccess('Gruppo creato con successo');
                }
                else
                {
                    Yii::app()->user->setFlash('success', 'Gruppo creato con successo');
                    $this->redirect(array('/group/view', 'id'=>$model->id));
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
        if(isset($_POST['Group']))
        {
            $model->setAttributes($_POST['Group']);
            if($model->save())
            {
                if(Yii::app()->request->isAjaxRequest)
                {
                    $this->ajaxSuccess('Gruppo modificato con successo');
                }
                else
                {
                    Yii::app()->user->setFlash('success', 'Gruppo modificato con successo');
                    $this->redirect(array('/group/view', 'id'=>$model->id));                                    
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
                'jquery.latest.js'=>false,
                'jquery.yiiactiveform.js'=>false,
                'tiny_mce_gzip.js'=>false,
                'jquery.tinymce.js'=>false,
                'embed.js'=>false,
                'bootstrap-transition.js'=>false,
                'bootstrap-button.js'=>false,
                'bootstrap-tooltip.js'=>false,
                'bootstrap-popover.js'=>false,
                'bootstrap-alert.js'=>false,
                'jquery-ui.min.latest.js'=>false,
                'dms.js'=>false
            );
            Yii::app()->clientScript->scriptMap = $script_map;
            $this->renderPartial('//group/_ajaxform', array('model'=>$model), false, true);
        }
        else
            $this->render('//group/update', array('model'=>$model));
    }
    
    public function actionDelete()
    {
        $model = $this->loadModel();
        if($model->delete())
            echo $this->ajaxSuccess ("Gruppo cancellato");
        else
            throw new CHttpException(500, "Impossibile cancellare il gruppo");
    }

    public function actionIndex()
    {
        $model = new Group('search');
        $model->unsetAttributes();
        if(isset($_GET['Group']))
            $model->attributes = $_GET['Group'];

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

    public function actionUsers()
    {
        $group = $this->loadModel();
        $model = new User('gsearch');
        if(isset($_GET['User']))
            $model->setAttributes ($_GET['User']);
        $model->group_id = $group->id;

        if(!isset($_GET['ajax'])){
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
                    'jquery.yiigridview.js'=>false
                );
                Yii::app()->clientScript->scriptMap = $script_map;            
                $this->renderPartial('userslist', array('model'=>$model, 'group'=>$group), false, true);                
            }
            else
                $this->render('userslist', array('model'=>$model, 'group'=>$group));
        }
        else{
            $this->renderPartial('userslist', array('model'=>$model, 'group'=>$group));       
        }
    }
    
    public function actionAddusers()
    {
        $group = $this->loadModel();
        if(!isset($_POST['user_ids']))
            throw new CHttpException(400, 'Richiesta non valida');

        if($group->addUsers($_POST['user_ids']))
            $this->ajaxSuccess ("Utenti aggiunti al gruppo");
        else
            throw new CHttpException(500, "Impossibile aggiungere gli utenti gruppo");
    }
    
    public function actionRemoveuser()
    {
        $group = $this->loadModel();
        if(!isset($_GET['user_id']))
            throw new CHttpException(400, 'Richiesta non valida');

        if($group->removeUser($_GET['user_id']))
            $this->ajaxSuccess ("Utente rimosso dal gruppo");
        else
            throw new CHttpException(500, "Impossibile rimuovere l'utente dal gruppo");        
    }

    public function actionAutocomplete($term)
    {
        $sql = "SELECT id, name, email FROM groups WHERE name LIKE :q OR email LIKE :q";
        $cmd = Yii::app()->db->createCommand($sql);
        $rows = $cmd->queryAll(true, array(':q'=>'%'.$term.'%'));
        $results = array();
        foreach($rows as $row)
        {
            $result = array();
            $result['id'] = $row['id'];
            $result['label'] = $row['name'].' ('.$row['email'].')';
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
                $this->_model=Group::model()->findByPk($_GET['id']);
            }

            if($this->_model===null)
                throw new CHttpException(404,'La pagina richiesta non esiste.');

        }

        return $this->_model;
    }
    
}