<?php
class SpendingController extends SecureController{
    
    private $_model = null;
    
    public $defaultAction = 'search';
    
    public function actionIndex()
    {
        $this->redirect(array('/spending/search'));
    }
    
    public function actionCreate()
    {
        $model = new Spending('create');
        $model->getTmpFiles();
        if(isset($_POST['Spending']))
        {
            $model->attributes = $_POST['Spending'];
            if($model->createSpending())
            {
                Yii::app()->user->setFlash('success', 'Spesa creata correttamente');
                $this->redirect(array('/spending/view', 'id'=>$model->id));
            }
        }
        $this->render('create', array('model'=>$model));
        
    }
    
    public function actionView()
    {
        $model = $this->loadModel();
        $this->render('view', array('model'=>$model));
    }
    
    public function actionUpdate()
    {
        $model = $this->loadModel();
        $model->scenario = 'update';
        $this->checkAuth($model);
        if(isset($_POST['Spending']))
        {
            $model->attributes = $_POST['Spending'];
            if($model->updateSpending())
            {
                Yii::app()->user->setFlash('success', 'Spesa aggiornata con successo');
                $this->redirect(array('/spending/view', 'id'=>$model->id));
            }
        }
        $this->render('update', array('model'=>$model));
    }
    
    public function actionDisable()
    {
        $model = $this->loadModel();
        $this->checkAuth($model);        
        $model->scenario = 'x';
        if($model->disable())
        {
            if(Yii::app()->request->isAjaxRequest)
            {
                $this->ajaxSuccess('Spesa rimossa con successo');
            }
            else
            {
                Yii::app()->user->setFlash('success', 'Spesa rimossa dall\'elenco con successo');
                $this->redirect(array('/spending/view', 'id'=>$model->id));
            }
        }
        else
        {
            if(Yii::app()->request->isAjaxRequest)
            {
                $this->ajaxError();
            }
            else
            {
                Yii::app()->user->setFlash('error', 'Non è stato possibile rimuovere la spesa dall\'elenco');
                $this->redirect(array('/spending/update', 'id'=>$model->id));
            }            
        }
        
    }
    
    public function actionDelete()
    {
        $model = $this->loadModel();
        $this->checkAuth($model);
        $model->scenario = 'x';
        if($model->deleteSpending())
        {
            if(Yii::app()->request->isAjaxRequest)
            {
                $this->ajaxSuccess('Spesa rimossa definitivamente');
            }
            else
            {
                Yii::app()->user->setFlash('success', 'Spesa rimossa definitivamente');
                $this->redirect(array('/spending/index'));
            }
        }
        else
        {
            if(Yii::app()->request->isAjaxRequest)
            {
                $this->ajaxError();
            }
            else
            {
                Yii::app()->user->setFlash('error', 'Non è stato possibile rimuovere definitivamente la spesa');
                $this->redirect(array('/spending/update', 'id'=>$model->id));
            }                        
        }
    }    
    
    public function actionEnable()
    {
        $model = $this->loadModel();
        $this->checkAuth($model);        
        $model->scenario = 'x';
        if($model->enable())
        {
            if(Yii::app()->request->isAjaxRequest)
            {
                $this->ajaxSuccess('Spesa ripristinata con successo');
            }
            else
            {
                Yii::app()->user->setFlash('success', 'Spesa ripristinata nell\'elenco con successo');
                $this->redirect(array('/spending/view', 'id'=>$model->id));
            }
        }
        else
        {
            if(Yii::app()->request->isAjaxRequest)
            {
                $this->ajaxError();
            }
            else
            {
                Yii::app()->user->setFlash('error', 'Non è stato possibile ripristinare la spesa nell\'elenco');
                $this->redirect(array('/spending/update', 'id'=>$model->id));
            }            
        }
        
    }
    
    public function actionUploadCV()
    {
        if(isset($_GET['id']))
        {
            $model = $this->loadModel();
            $this->checkAuth($model);            
            $model->scenario = 'cv_upload';
            $tmp = false;
        }
        else
        {
            $model = new Spending('cv_upload');
            $tmp = true;
        }
        
        $model->cv_file = CUploadedFile::getInstance($model, 'cv_file');
        if($model->validate() && $model->processCVUpload($tmp))
        {
            $ret = array();
            $ret['success'] = 1;
            $ret['message'] = 'File CV caricato con successo';
            $ret['filename'] = $model->cv_file->name;
            $ret['filesize'] = $model->cv_file->size;
            echo CJSON::encode($ret);
            Yii::app()->end();
        }
        else
        {
            $this->ajaxError($model->getErrors());
        }
        
    }
    
    public function actionUploadProject()
    {
        if(isset($_GET['id']))
        {
            $model = $this->loadModel();
            $this->checkAuth($model);            
            $model->scenario = 'project_upload';
            $tmp = false;
        }
        else
        {
            $model = new Spending('project_upload');
            $tmp = true;
        }
        
        $model->project_file = CUploadedFile::getInstance($model, 'project_file');
        if($model->validate() && $model->processProjectUpload($tmp))
        {
            $ret = array();
            $ret['success'] = 1;
            $ret['message'] = 'File progetto caricato con successo';
            $ret['filename'] = $model->project_file->name;
            $ret['filesize'] = $model->project_file->size;
            echo CJSON::encode($ret);
            Yii::app()->end();
        }
        else
        {
            $this->ajaxError($model->getErrors());
        }
                
    }
    
    public function actionUploadContract()
    {
        if(isset($_GET['id']))
        {
            $model = $this->loadModel();
            $this->checkAuth($model);            
            $model->scenario = 'contract_upload';
            $tmp = false;
        }
        else
        {
            $model = new Spending('contract_upload');
            $tmp = true;
        }
        
        $model->contract_file = CUploadedFile::getInstance($model, 'contract_file');
        if($model->validate() && $model->processContractUpload($tmp))
        {
            $ret = array();
            $ret['success'] = 1;
            $ret['message'] = 'File contratto caricato con successo';
            $ret['filename'] = $model->contract_file->name;
            $ret['filesize'] = $model->contract_file->size;
            echo CJSON::encode($ret);
            Yii::app()->end();
        }
        else
        {
            $this->ajaxError($model->getErrors());
        }
                
    }
    
    public function actionUploadCapitulate()
    {
        if(isset($_GET['id']))
        {
            $model = $this->loadModel();
            $this->checkAuth($model);            
            $model->scenario = 'capitulate_upload';
            $tmp = false;
        }
        else
        {
            $model = new Spending('capitulate_upload');
            $tmp = true;
        }
        
        $model->capitulate_file = CUploadedFile::getInstance($model, 'capitulate_file');
        if($model->validate() && $model->processCapitulateUpload($tmp))
        {
            $ret = array();
            $ret['success'] = 1;
            $ret['message'] = 'File capitolato caricato con successo';
            $ret['filename'] = $model->capitulate_file->name;
            $ret['filesize'] = $model->capitulate_file->size;
            echo CJSON::encode($ret);
            Yii::app()->end();
        }
        else
        {
            $this->ajaxError($model->getErrors());
        }
                
    }
    
    public function actionUploadOther()
    {
        if(isset($_GET['id']))
        {
            $model = $this->loadModel();
            $this->checkAuth($model);            
            $model->scenario = 'other_upload';
            $tmp = false;
        }
        else
        {
            $model = new Spending('other_upload');
            $tmp = true;
        }
        
        $model->other_file = CUploadedFile::getInstance($model, 'other_file');
        if($model->validate() && $model->processOtherUpload($tmp))
        {
            $ret = array();
            $ret['success'] = 1;
            $ret['message'] = 'File altra documentazione caricato con successo';
            $ret['filename'] = $model->other_file->name;
            $ret['filesize'] = $model->other_file->size;
            $ret['can_add'] = $model->canAddNewOther($tmp)?1:0;
            echo CJSON::encode($ret);
            Yii::app()->end();
        }
        else
        {
            $this->ajaxError($model->getErrors());
        }
                        
    }    
    
    public function actionDownloadCV()
    {
        $model = $this->loadModel();
        $model->downloadCV();        
    }

    public function actionDownloadProject()
    {
        $model = $this->loadModel();
        $model->downloadProject();        
    }
    
    public function actionDownloadContract()
    {
        $model = $this->loadModel();
        $model->downloadContract();
    }
    
    public function actionDownloadCapitulate()
    {
        $model = $this->loadModel();
        $model->downloadCapitulate();
    }
    
    public function actionDownloadOther($filename)
    {
        $model = $this->loadModel();
        $model->downloadOther($filename);
    }
    
    public function actionDeleteCV()
    {
        if(isset($_GET['id']))
        {
            $model = $this->loadModel();
            $this->checkAuth($model);
            $tmp = false;
        }
        else
        {
            $model =  new Spending();
            $tmp = true;
        }
        
        if($model->deleteCV($tmp))
            $this->ajaxSuccess ("File rimosso con successo");
        else
            $this->ajaxError();
    }
    
    public function actionDeleteProject()
    {
        if(isset($_GET['id']))
        {
            $model = $this->loadModel();
            $this->checkAuth($model);            
            $tmp = false;
        }
        else
        {
            $model =  new Spending();
            $tmp = true;
        }
        
        if($model->deleteProject($tmp))
            $this->ajaxSuccess ("File rimosso con successo");
        else
            $this->ajaxError();        
    }
    
    public function actionDeleteContract()
    {
        if(isset($_GET['id']))
        {
            $model = $this->loadModel();
            $this->checkAuth($model);            
            $tmp = false;
        }
        else
        {
            $model =  new Spending();
            $tmp = true;
        }
        
        if($model->deleteContract($tmp))
            $this->ajaxSuccess ("File rimosso con successo");
        else
            $this->ajaxError();        
    }
    
    public function actionDeleteCapitulate()
    {
        if(isset($_GET['id']))
        {
            $model = $this->loadModel();
            $this->checkAuth($model);            
            $tmp = false;
        }
        else
        {
            $model =  new Spending();
            $tmp = true;
        }
        
        if($model->deleteCapitulate($tmp))
            $this->ajaxSuccess ("File rimosso con successo");
        else
            $this->ajaxError();        
    }    
    
    public function actionDeleteOther($filename)
    {
        if(isset($_GET['id']))
        {
            $model = $this->loadModel();
            $this->checkAuth($model);            
            $tmp = false;
        }
        else
        {
            $model =  new Spending();
            $tmp = true;
        }
        
        if($model->deleteOther($filename, $tmp))
            $this->ajaxSuccess ("File rimosso con successo");
        else
            $this->ajaxError();                
    }    
    
    public function actionSearch()
    {
        $model = new Spending('search');
        $model->unsetAttributes();
        if(isset($_GET['Spending']))
            $model->attributes = $_GET['Spending'];

        $params =array(
            'model'=>$model,
            'search_type' => Spending::SEARCH_ALL            
        );

        if(!isset($_GET['ajax']))
            $this->render('list', $params);
        else
            $this->renderPartial('list', $params);  
    }
    
    public function actionOwned()
    {
        $model = new Spending('search');
        $model->unsetAttributes();
        if(isset($_GET['Spending']))
            $model->attributes = $_GET['Spending'];

        $params =array(
            'model'=>$model,
            'search_type' => Spending::SEARCH_MY
        );

        if(!isset($_GET['ajax']))
            $this->render('list', $params);
        else
            $this->renderPartial('list', $params);  
    }
    
    public function actionDisabled()
    {
        $model = new Spending('search');
        $model->unsetAttributes();
        if(isset($_GET['Spending']))
            $model->attributes = $_GET['Spending'];

        $params =array(
            'model'=>$model,
            'search_type' => Spending::SEARCH_DISABLED
        );

        if(!isset($_GET['ajax']))
            $this->render('list', $params);
        else
            $this->renderPartial('list', $params);  
    }
    
    protected function loadModel()
    {
        if($this->_model===null)
        {
            if(isset($_GET['id']))
            {
                $this->_model=Spending::model()->findByPk($_GET['id']);
            }

            if($this->_model===null)
                throw new CHttpException(404,'La pagina richiesta non esiste.');

            if($this->_model->status === Document::DISABLED_STATUS)
            {
                if(!Yii::app()->user->isAdmin() && !$this->_model->creator_id==Yii::app()->user->id)
                {
                     throw new CHttpException(404,'La pagina richiesta non esiste.');   
                }
            }
        }

        return $this->_model;
    }    
    
    protected function checkAuth($model)
    {
        if(Yii::app()->user->isAdmin() || $model->creator_id==Yii::app()->user_id)
            return true;
        else
            throw new CHttpException(403, 'Non si dispone dei permessi sufficienti per eseguire l\'operazione richiesta');
    }
}