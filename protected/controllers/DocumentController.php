<?php
class DocumentController extends SecureController{
    
    private $_model = null;
    
    public $defaultAction = 'search';
    
    public function actionIndex()
    {
        $model = new Document('dashboard');
        $model->unsetAttributes();
        if(isset($_GET['Document']))
            $model->attributes = $_GET['Document'];

        $params =array(
            'model'=>$model,
        );

        if(!isset($_GET['ajax']))
            $this->render('dashboard', $params);
        else
            $this->renderPartial('dashboard', $params);          
    }
    
    public function actionPending($force_reload = 0)
    {
        // load user's folders
        if(Yii::app()->user->isAdmin())
        {
            $groups = Group::model()->findAll();
            $groups_array = array();
            foreach($groups as $group)
                $groups_array[$group->id] = array('name'=>$group->name, 'folder_name'=>$group->folder_name);
        }
        else
            $groups_array = Yii::app()->user->getGroups();
            
        // scan files sorted by date
        $fm = new FileManager();
        $last_check = Yii::app()->user->getState('last_update_check');
        if(Yii::app()->request->isAjaxRequest)
        {
            // check if there are updates
            $has_update = ($force_reload==1 || $fm->hasUpdates($groups_array, Yii::app()->user->id, $last_check));
            if($has_update)
                $documents = $fm->getPendingDocuments($groups_array, Yii::app()->user->id, $last_check);
        }
        else
            $documents = $fm->getPendingDocuments($groups_array, Yii::app()->user->id, $last_check);
   
        Yii::app()->user->setState('last_update_check', time());

        if(Yii::app()->request->isAjaxRequest){
            if($has_update==1)
                $content = $this->renderPartial('pending', array('documents'=>$documents, 'groups'=>$groups_array, 'is_ajax'=>true), true);
            else
                $content = false;
            echo CJSON::encode(array('has_update'=>$has_update?1:0, 'content'=>$content));
        }
        else
            $this->render('pending', array('documents'=>$documents, 'groups'=>$groups_array));
    }
    
    public function actionDeletepending($document_name, $group_id = 0)
    {
        $document_name = basename($document_name); // sanitize
        if($group_id>0)
        {
            if(!Yii::app()->user->belongsToGroup($group_id))
                throw new CHttpException(403, 'Non autorizzato');        
        
            // group_folder
            $group_folder_name = Yii::app()->user->getGroupFolderName($group_id);
            $dm = new DocumentManager($group_folder_name, $document_name, DocumentManager::GROUP_PENDING_TYPE);
        }
        else
        {
            $dm = new DocumentManager(Yii::app()->user->id, $document_name, DocumentManager::USER_PENDING_TYPE);            
        }
        
        if($dm->delete())
        {
            if(Yii::app()->request->isAjaxRequest)
            {
                $this->ajaxSuccess("Document cancellato");
            }
            else
            {
                Yii::app()->user->setFlash('success', 'Documento cancellato');
                $this->redirect('/document/pending');
            }
        }
        else
        {
            if(Yii::app()->request->isAjaxRequest)
            {
                $this->ajaxError(array("Impossibile cancellare il documento"));
            }
            else
            {
                Yii::app()->user->setFlash('error', 'Impossibile cancellare il documento');
                $this->redirect('/document/pending');
            }            
        }
    }
    
    public function actionPreview($document_name, $group_id = 0, $page = 0)
    {
        $document_name = basename($document_name); // sanitize
        if($group_id>0)
        {
            if(!Yii::app()->user->belongsToGroup($group_id))
                throw new CHttpException(403, 'Non autorizzato');
            // group_folder
            $group_folder_name = Yii::app()->user->getGroupFolderName($group_id);            
            $dm = new DocumentManager($group_folder_name, $document_name, DocumentManager::GROUP_PENDING_TYPE);
        }
        else
        {
            $dm = new DocumentManager(Yii::app()->user->id, $document_name, DocumentManager::USER_PENDING_TYPE);
        }
        $pm = new PreviewManager($dm);
        header('Content-Type: image/jpeg');
        readfile($pm->getPreview(intval($page)));
    }
    
    public function actionPreviewdoc($page = 0)
    {
        $model = $this->loadModel();
        if(!Yii::app()->user->hasDocumentPrivilege($model->id, AclManager::PERMISSION_READ))
            throw new CHttpException(403, 'Azione non consentita');
        $pm = new PreviewManager($model);
        header('Content-Type: image/jpeg');
        readfile($pm->getPreview(intval($page)));
    }
    
    public function actionThumbnail($document_name, $group_id = 0)
    {
        if($group_id>0)
        {
            $document_name = basename($document_name); // sanitize
            if(!Yii::app()->user->belongsToGroup($group_id))
                throw new CHttpException(403, 'Non autorizzato');
            // group_folder
            $group_folder_name = Yii::app()->user->getGroupFolderName($group_id);            
            $dm = new DocumentManager($group_folder_name, $document_name, DocumentManager::GROUP_PENDING_TYPE);
        }
        else
        {
            $dm = new DocumentManager(Yii::app()->user->id, $document_name, DocumentManager::USER_PENDING_TYPE);            
        }
        $pm = new PreviewManager($dm);
        header('Content-Type: image/jpeg');
        readfile($pm->getThumbnail());
    }
    
    public function actionProtocol($document_name, $group_id = 0)
    {
        if($group_id>0)
        {
            $document_name = basename($document_name); // sanitize
            if(!Yii::app()->user->belongsToGroup($group_id))
                throw new CHttpException(403, 'Non autorizzato');
            // group_folder
            $group_folder_name = Yii::app()->user->getGroupFolderName($group_id);            
            $dm = new DocumentManager($group_folder_name, $document_name, DocumentManager::GROUP_PENDING_TYPE);
        }
        else
        {
            $dm = new DocumentManager(Yii::app()->user->id, $document_name, DocumentManager::USER_PENDING_TYPE);                        
        }
        $model = new Document('protocol');
        
        $pm = new PreviewManager($dm);
        $total_pages = intval($pm->getDocumentInfo());        

        // lock check 

        // lock document
        
        // if $_POST
        if(isset($_POST['Document']))
        {
            $model->tmp_path = $dm->getPath();
            $model->document_manager = $dm;
            $model->num_pages = $total_pages;
            $model->main_document_type = Document::INBOX;
            
            $model->attributes = $_POST['Document'];
            if($model->protocolDocument())
            {
                Yii::app()->user->setFlash('success', 'Document Protocollato #'.$model->identifier);
                $this->redirect(array('/document/pending'));
            }
        }
        // render form
        $this->render('protocol', array('model'=>$model, 'scenario'=>'protocol', 'group_id'=>$group_id, 'document_name'=>$document_name, 'total_pages'=>$total_pages));
    }
    
    public function actionPublish($document_name, $group_id = 0)
    {
        if($group_id>0)
        {
            $document_name = basename($document_name); // sanitize
            if(!Yii::app()->user->belongsToGroup($group_id))
                throw new CHttpException(403, 'Non autorizzato');
            // group_folder
            $group_folder_name = Yii::app()->user->getGroupFolderName($group_id);            
            $dm = new DocumentManager($group_folder_name, $document_name, DocumentManager::GROUP_PENDING_TYPE);
        }
        else
        {
            $dm = new DocumentManager(Yii::app()->user->id, $document_name, DocumentManager::USER_PENDING_TYPE);                        
        }
        $model = new Document('publish');
        
        $pm = new PreviewManager($dm);
        $total_pages = intval($pm->getDocumentInfo());        

        // lock check 

        // lock document
        
        // if $_POST
        if(isset($_POST['Document']))
        {
            $model->tmp_path = $dm->getPath();
            $model->document_manager = $dm;
            $model->num_pages = $total_pages;
            $model->main_document_type = Document::OUTGOING;
            
            $model->attributes = $_POST['Document'];
            if($model->protocolDocument())
            {
                Yii::app()->user->setFlash('success', 'Documento creato: '.$model->getTitle());
                $this->redirect(array('/document/index'));
            }
        }
        // render form
        $this->render('protocol', array('model'=>$model, 'scenario'=>'publish', 'group_id'=>$group_id, 'document_name'=>$document_name, 'total_pages'=>$total_pages));
    }    
    
    public function actionArchive($document_name, $group_id = 0)
    {
        
        if($group_id>0)
        {
            $document_name = basename($document_name); // sanitize
            if(!Yii::app()->user->belongsToGroup($group_id))
                throw new CHttpException(403, 'Non autorizzato');
            // group_folder
            $group_folder_name = Yii::app()->user->getGroupFolderName($group_id);            
            $dm = new DocumentManager($group_folder_name, $document_name, DocumentManager::GROUP_PENDING_TYPE);
        }
        else
        {
            $dm = new DocumentManager(Yii::app()->user->id, $document_name, DocumentManager::USER_PENDING_TYPE);                        
        }

        $model = new Document('archive');

        $pm = new PreviewManager($dm);
        $total_pages = intval($pm->getDocumentInfo());  
        
        // lock check 

        // lock document
        
        // if $_POST
        if(isset($_POST['Document']))
        {
            $model->tmp_path = $dm->getPath();
            $model->document_manager = $dm;
            $model->num_pages = $total_pages;
            $model->main_document_type = Document::INTERNAL_USE_TYPE;
            
            $model->attributes = $_POST['Document'];
            if($model->protocolDocument())
            {
                Yii::app()->user->setFlash('success', 'Document Archiviato');
                $this->redirect(array('/document/pending'));
            }
        }

        // render form

        $this->render('protocol', array('model'=>$model, 'scenario'=>'archive', 'group_id'=>$group_id, 'document_name'=>$document_name, 'total_pages'=>$total_pages));        // render form
    }

    public function actionPreviewpdf($document_name, $group_id = 0)
    {
        if($group_id>0)
        {
            $document_name = basename($document_name); // sanitize
            if(!Yii::app()->user->belongsToGroup($group_id))
                throw new CHttpException(403, 'Non autorizzato  ');
            // group_folder
            $group_folder_name = Yii::app()->user->getGroupFolderName($group_id);            
            $dm = new DocumentManager($group_folder_name, $document_name, DocumentManager::GROUP_PENDING_TYPE);            
        }
        else
        {
            $dm = new DocumentManager(Yii::app()->user->id, $document_name, DocumentManager::USER_PENDING_TYPE);                        
        }

        $dm->download();
    }
    
    public function actionView()
    {
        $model = $this->loadModel();
        if(!Yii::app()->user->hasDocumentPrivilege($model->id, AclManager::PERMISSION_READ))
        {
            $this->redirect(array('/document/newticket', 'id'=>$model->id));
        }
        
        $revisions = DocumentHistory::model()->findLatest($model->id);
        $view_name = 'view_';
        if($model->main_document_type == Document::INTERNAL_USE_TYPE)
            $view_name .= 'archive';
        elseif($model->main_document_type == Document::INBOX)
            $view_name .= 'protocol';
        else
            $view_name .= 'publish';
        $this->render($view_name, array('model'=>$model, 'revisions'=>$revisions['rows'], 'revisions_count'=>$revisions['count']));
    }
    
    public function actionViewpdf()
    {
        $model = $this->loadModel();
        if(!Yii::app()->user->hasDocumentPrivilege($model->id, AclManager::PERMISSION_READ))
            throw new CHttpException(403, 'Azione non consentita');        
        $model->download();
    }

    public function actionUpdate()
    {
        $model = $this->loadModel();
        
        //bug fixed
            if($model->publication_requested == 1 && $_POST['Document']['publication_requested'] == 0) $model->is_dirty = 1;
        
        if(!Yii::app()->user->hasDocumentPrivilege($model->id, AclManager::PERMISSION_WRITE) && !Role::model()->findRole(4))
            throw new CHttpException(403, 'Azione non consentita');
        
        $model->loadTagsArray();
        
        if($model->main_document_type == Document::INTERNAL_USE_TYPE)
            $name = 'archive';
        elseif($model->main_document_type == Document::INBOX)
        {
            $model->loadSenderData();
            $name = 'protocol';
        }
        else
            $name = 'publish';
        
        if(Yii::app()->user->hasDocumentPrivilege($model->id, AclManager::PERMISSION_ADMIN) || Role::model()->findRole(4))
            $model->scenario = $name.'_admin';
        else
            $model->scenario = $name.'_update';
 
        if(isset($_POST['Document']))
        {
            $model->attributes = $_POST['Document'];
            $model->document_type = $_POST['Document']['document_type']; //patched
            
            if($model->revision>$_POST['Document']['revision'])
            {
                Yii::app()->user->setFlash('warning', 'Hai provato ad aggiornare una versione del documento più vecchia della recente. Riapplica i cambiamenti ora.');
                $this->redirect(array('/document/update', 'id'=>$model->id));
            }
            
            //PATCH: Publication number 
            if ($name == 'publish' && empty($model->publication_number) ) :                           
                    $criteria = new CDbCriteria();
                    $criteria->order = "id DESC";
                    $criteria->condition = "publication_number > ''";
                    $criteria->limit = 1;
                    $modelDoc = Document::model()->findAll($criteria);           
                    $model->publication_number = (isset($modelDoc[0]->publication_number)) ? $modelDoc[0]->publication_number + 1 : 1;                    
            endif;
            
            
            
            if($model->updateDocument())
            {
                Yii::app()->user->setFlash('success', 'Documento modificato');
                $this->redirect(array('/document/view', 'id'=>$model->id));
            }
        }

        $this->render('update_'.$name, array('model'=>$model));        
    }
    
    public function actionDisable()
    {
        $model = $this->loadModel();
        $model->scenario = 'x';
        if(!Yii::app()->user->hasDocumentPrivilege($model->id, AclManager::PERMISSION_ADMIN))
            throw new CHttpException(403, 'Azione non consentita');        
        if($model->disable())
        {
            if(Yii::app()->request->isAjaxRequest)
                $this->ajaxSuccess ('Documento rimosso con successo');
            else
            {
                Yii::app()->user->setFlash('success', 'Documento rimosso con successo');
                $this->redirect(array('/document/view', 'id'=>$model->id));
            }
        }
        else
        {
            if(Yii::app()->request->isAjaxRequest)
                $this->ajaxError (array('Impossibile rimuovere il documento'));
            else
            {
                Yii::app()->user->setFlash('error', 'Impossibile rimuovere il documento');
                $this->redirect(array('/document/view', 'id'=>$model->id));
            }            
        }
    }
    
    public function actionDelete()
    {
        $model = $this->loadModel();
        $model->scenario = 'x';
        if(!Yii::app()->user->isAdmin() && $model->creator_id != Yii::app()->user->id && !Role::model()->findRole(4))
            throw new CHttpException(403, 'Azione non consentita');        
        
        if($model->deleteDocument())
        {
            if(Yii::app()->request->isAjaxRequest)
                $this->ajaxSuccess ('Documento eliminato definitivamente');
            else
            {
                Yii::app()->user->setFlash('success', 'Documento eliminato definitivamente');
                $this->redirect(array('/document/disabled'));
            }
        }
        else
        {
            if(Yii::app()->request->isAjaxRequest)
                $this->ajaxError (array('Impossibile eliminare il documento'));
            else
            {
                Yii::app()->user->setFlash('error', 'Impossibile eliminare il documento');
                $this->redirect(array('/document/disabled'));
            }            
        }
    }    
    
    public function actionEnable()
    {
        $model = $this->loadModel();
        $model->scenario = 'x';
        if(!Yii::app()->user->hasDocumentPrivilege($model->id, AclManager::PERMISSION_ADMIN))
            throw new CHttpException(403, 'Azione non consentita');        
        if($model->enable())
        {
            if(Yii::app()->request->isAjaxRequest)
                $this->ajaxSuccess ('Documento rimosso con successo');
            else
            {
                Yii::app()->user->setFlash('success', 'Documento rimosso con successo');
                $this->redirect(array('/document/view', 'id'=>$model->id));
            }
        }
        else
        {
            if(Yii::app()->request->isAjaxRequest)
                $this->ajaxError (array('Impossibile rimuovere il documento'));
            else
            {
                Yii::app()->user->setFlash('error', 'Impossibile rimuovere il documento');
                $this->redirect(array('/document/view', 'id'=>$model->id));
            }            
        }        
    }
    
    public function actionUpdaterights()
    {
        $model = $this->loadModel();
        if(!Yii::app()->user->isAdmin() && !$model->creator_id==Yii::app()->user->id)
            throw new CHttpException(403, 'Azione non consentita');        
        
        if(isset($_POST['user_ids']))
        {
            if(DocumentRight::model()->updateRights($model->id, $_POST))
            {
                $rights = DocumentRight::model()->findAllByDocumentId($model->id);
                echo CJSON::encode($rights);
                Yii::app()->end();
            }
            else
                throw new CHttpException(500, 'Impossibile aggiornare i permessi');
        }
        throw new CHttpException(400, 'Richiesta non valida');
    }
    
    public function actionRights()
    {
        $model = $this->loadModel();
        if(!Yii::app()->user->hasDocumentPrivilege($model->id, AclManager::PERMISSION_ADMIN))
            throw new CHttpException(403, 'Azione non consentita');
        
        $rights = DocumentRight::model()->findAllByDocumentId($model->id);
        echo CJSON::encode($rights);
        Yii::app()->end();
    }

    public function actionUpload()
    {
        $model = new UploadDocumentForm();
        
        if(isset($_POST['UploadDocumentForm']))
        {
            $model->user_id = Yii::app()->user->id;
            $model->attributes = $_POST['UploadDocumentForm'];
            $model->document_file = CUploadedFile::getInstance($model, 'document_file');
            if($model->upload())
            {
                if(Yii::app()->authgateway->isAllowed('document', 'protocol'))
                    $success_url = Yii::app()->createAbsoluteUrl('/document/publish', array('document_name'=>$model->document_file->name));
                else
                    $success_url = Yii::app()->createAbsoluteUrl('/document/archive', array('document_name'=>$model->document_file->name));                    
                
                $ret = array(
                    'success'=>1,
                    'success_url'=>$success_url
                );

                echo CJSON::encode($ret);
                Yii::app()->end();
            }
            else
            {
                $this->ajaxError($model->getErrors());
            }
        }
        else
            throw new CHttpException(400, 'Richiesta non valida');
    }
    
    public function actionRemoveUser($user_id)
    {
        $model = $this->loadModel();
        if(!Yii::app()->user->isAdmin() && !$model->creator_id==Yii::app()->user->id)
            throw new CHttpException(403, 'Azione non consentita');        
        
        if($user_id != Yii::app()->user->id)
        {
            DocumentRight::model()->deleteAllByAttributes(array('document_id'=>$model->id, 'user_id'=>$user_id));
            Yii::app()->end();            
        }
        else
        {
            throw new CHttpException(403, 'Azione non consentita');
        }
    }
    
    public function actionRemoveGroup($group_id)
    {
        $model = $this->loadModel();
        if(!Yii::app()->user->isAdmin() && !$model->creator_id==Yii::app()->user->id)
            throw new CHttpException(403, 'Azione non consentita');        
        
        DocumentRight::model()->deleteAllByAttributes(array('document_id'=>$model->id, 'group_id'=>$group_id));
        Yii::app()->end();
    }
    
    public function actionSearch($doc_type = Document::INBOX)
    {
        $tagsmodel = new DocumentSearchForm('tags');        
        $datemodel = new DocumentSearchForm('date');                
        $model_array = array('tagsmodel'=>$tagsmodel, 'datemodel'=>$datemodel);            

            
        if($doc_type == Document::OUTGOING)
        {
            $idmodel = new DocumentSearchForm('identifier');
            $template = 'search_publish';
            $model_array['idmodel'] = $idmodel;
            $result_template = '_publishsearchresult';                        
        }
        elseif($doc_type == Document::INTERNAL_USE_TYPE)
        {
            $template = 'search_archive';            
            $result_template = '_archivesearchresult';            
        }
        else
        {
            $idmodel = new DocumentSearchForm('identifier');
            $template = 'search_protocol';     
            $result_template = '_protocolsearchresult';
            $model_array['idmodel'] = $idmodel;            
        }

        $searchform_template = $template;

        if(isset($_GET['DocumentSearchForm']))
        {
            if(isset($_GET['s_type']) && $_GET['s_type']=='id')
            {
                $idmodel->attributes = $_GET['DocumentSearchForm'];
                $idmodel->search($doc_type);
                if($idmodel->hasResults() && $idmodel->getResultsCount()==1)
                {
                    $document = $idmodel->getFirst();
                    $this->redirect(array('/document/view', 'id'=>$document['id']));
                }
            }
            elseif(isset($_GET['s_type']) && $_GET['s_type']=='tags')
            {
                // search by tags
                $tagsmodel->attributes = $_GET['DocumentSearchForm'];
                $tagsmodel->search($doc_type);
                if($tagsmodel->hasResults() && $tagsmodel->getResultsCount()==1)
                {
                    $document = $tagsmodel->getFirst();
                    $this->redirect(array('/document/view', 'id'=>$document['id']));
                }                
            }
            else
            {
                // search by tags
                $datemodel->attributes = $_GET['DocumentSearchForm'];
                $datemodel->search($doc_type);
                if($datemodel->hasResults() && $datemodel->getResultsCount()==1)
                {
                    $document = $datemodel->getFirst();
                    $this->redirect(array('/document/view', 'id'=>$document['id']));
                }                                
            }
            $template = 'searchresults';
        }
        
        if(!isset($_GET['ajax']))
            $this->render($template, array_merge(array('searchform_template' => $searchform_template, 'result_template'=>$result_template, 'doc_type' => $doc_type), $model_array));
        else
            $this->renderPartial($template, array_merge(array('searchform_template' => $searchform_template, 'result_template'=>$result_template, 'doc_type' => $doc_type), $model_array));

    }
    
    public function actionHistory()
    {
        $document = $this->loadModel();
        if(!Yii::app()->user->hasDocumentPrivilege($document->id, AclManager::PERMISSION_READ))
            throw new CHttpException(403, 'Azione non consentita');        
        
        $model = new DocumentHistory('search');
        if(isset($_GET['DocumentHistory']))
        {
            $model->attributes = $_GET['DocumentHistory'];
            $model->document_id = $document->id;
        }
        
        $params =array(
            'model'=>$model,
            'document'=>$document
        );

        if(!isset($_GET['ajax']))
            $this->render('//documenthistory/list', $params);
        else
            $this->renderPartial('//documenthistory/list', $params);        
    }

    public function actionMy()
    {
        $model = new Document('my');
        //$model->unsetAttributes();
        if(isset($_GET['Document']))
            $model->attributes = $_GET['Document'];

        $params =array(
            'model'=>$model,
        );

        if(!isset($_GET['ajax']))
            $this->render('my', $params);
        else
            $this->renderPartial('my', $params);  
    }
    
    public function actionPublic()
    {
        $model = new Document('public');
        //$model->unsetAttributes();
        if(isset($_GET['Document']))
            $model->attributes = $_GET['Document'];

        $params =array(
            'model'=>$model,
        );

        if(!isset($_GET['ajax']))
            $this->render('public', $params);
        else
            $this->renderPartial('public', $params);  
    }

    public function actionDisabled()
    {
        $model = new Document('disabled');
//        $model->unsetAttributes();
        if(isset($_GET['Document']))
            $model->attributes = $_GET['Document'];

        $params =array(
            'model'=>$model,
        );

        if(!isset($_GET['ajax']))
            $this->render('disabled', $params);
        else
            $this->renderPartial('disabled', $params);  
    }    
    
    public function actionOwned()
    {
        $model = new Document('created');
        //$model->unsetAttributes();
        if(isset($_GET['Document']))
            $model->attributes = $_GET['Document'];

        $params =array(
            'model'=>$model,
        );

        if(!isset($_GET['ajax']))
            $this->render('created', $params);
        else
            $this->renderPartial('created', $params);  
    }    

    public function actionNewticket()
    {
        $model = $this->loadModel();
        // just users without access permission to the document can open tickets
        if(Yii::app()->user->hasDocumentPrivilege($model->id, AclManager::PERMISSION_READ))
            throw new CHttpException(400, 'Azione non consentita');        
        
        $ticket = new Ticket('create');
        $ticket->document_id = $model->id;
        $ticket->user_id = Yii::app()->user->id;
        
        if(isset($_POST['Ticket']))
        {
            $ticket->attributes = $_POST['Ticket'];
            if($ticket->createTicket())
            {
                if(Yii::app()->request->isAjaxRequest)
                    $this->ajaxSuccess ('Ticket creato con successo');
                else
                {
                    Yii::app()->user->setFlash('success', 'Ticket #'.$ticket->id.' creato con successo');
                    $this->redirect('/ticket/my');
                }
            }
            else
            {
                if(Yii::app()->request->isAjaxRequest)
                    $this->ajaxError ($ticket->getErrors ());
            }            
        }
        
        $last_tickets = Ticket::model()->getLastByDocument($model->id, Yii::app()->user->id, 5);
        
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
            $this->renderPartial('//ticket/_ajaxform', array('model'=>$ticket, 'document'=>$model, 'last_tickets'=>$last_tickets), false, true);
        }
        else
            $this->render('//ticket/create', array('model'=>$ticket, 'document'=>$model, 'last_tickets'=>$last_tickets));
    }
    
    protected function loadModel()
    {
        if($this->_model===null)
        {
            if(isset($_GET['id']))
            {
                $this->_model=Document::model()->findByPk($_GET['id']);
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
}