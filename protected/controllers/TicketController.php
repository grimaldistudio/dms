<?php
class TicketController extends SecureController{

    private $_model = null;
    
    public function actionIndex()
    {
        $model = new Ticket('search');
        // only admin!!
        // set group_ids
        // set user_id
        $model->unsetAttributes();
        if(isset($_GET['Ticket']))
            $model->attributes = $_GET['Ticket'];

        $params =array(
            'model'=>$model,
        );

        if(!isset($_GET['ajax']))
            $this->render('//ticket/list', $params);
        else
            $this->renderPartial('//ticket/list', $params);          
    }
    
    public function actionMy()
    {
        $model = new Ticket('my');
        // set user_id
        $model->unsetAttributes();
        if(isset($_GET['Ticket']))
            $model->attributes = $_GET['Ticket'];

        $params =array(
            'model'=>$model,
        );

        if(!isset($_GET['ajax']))
            $this->render('//ticket/my', $params);
        else
            $this->renderPartial('//ticket/my', $params);          
    }
    
    public function actionUpdate()
    {
        $model = $this->loadModel();
        $model->setScenario('update');
        $this->checkWritePermissions();
        if(!$model->isOpen())
            throw new CHttpException(400, 'Ticket già chiuso');
        
        if(isset($_POST['Ticket']))
        {
            $model->setAttributes($_POST['Ticket']);
            if($model->closeTicket())
            {
                if(Yii::app()->request->isAjaxRequest)
                {
                    $this->ajaxSuccess('Ticket modificato con successo');
                }
                else
                {
                    Yii::app()->user->setFlash('success', 'Ticket modificato con successo');
                    $this->redirect(array('/ticket/index'));                                    
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
            $this->renderPartial('//ticket/_ajaxupdateform', array('model'=>$model), false, true);
        }
        else
            $this->render('//ticket/update', array('model'=>$model));        
    }

    public function actionDelete()
    {
        $model = $this->loadModel();
        $this->checkDeletePermissions();
        if($model->canDelete() && $model->delete())
            echo $this->ajaxSuccess ("Ticket cancellato");
        else
            throw new CHttpException(500, "Impossibile cancellare il Ticket");        
    }
    
    public function actionView()
    {
        $model = $this->loadModel();
        $this->checkReadPermissions();
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
    
    protected function loadModel()
    {
        if($this->_model===null)
        {
            if(isset($_GET['id']))
            {
                $this->_model=Ticket::model()->findByPk($_GET['id']);
            }

            if($this->_model===null)
                throw new CHttpException(404,'La pagina richiesta non esiste.');

        }

        return $this->_model;
    }
    
    protected function checkReadPermissions()
    {
        $model = $this->loadModel();
        if($model->user_id == Yii::app()->user->id || Yii::app()->user->isAdmin() || $model->replier_id == Yii::app()->user->id || $model->document->creator_id == Yii::app()->user->id)
            return true;
        throw new CHttpException(403, 'Permesso negato');
    }

    protected function checkWritePermissions()
    {
        $model = $this->loadModel();
        if(Yii::app()->user->isAdmin() || $model->document->creator_id == Yii::app()->user->id)
            return true;
        throw new CHttpException(403, 'Permesso negato');        
    }
    
    protected function checkDeletePermissions()
    {
        $model = $this->loadModel();
        if($model->user_id == Yii::app()->user->id)
            return true;
        throw new CHttpException(403, 'Permesso negato');               
    }
}

?>
