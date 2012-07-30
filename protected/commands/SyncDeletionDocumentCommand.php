<?php

require_once Yii::getPathOfAlias('vendors').DIRECTORY_SEPARATOR.'guzzle'.DIRECTORY_SEPARATOR.'guzzle.phar';

class SyncDeletionDocumentCommand extends CConsoleCommand{
    
    public function run($args)
    {
        $max_deletion_documents = Yii::app()->params['max_deletion_documents'];        
        $cmd = Yii::app()->db->createCommand("SELECT id, document_id FROM documents_deleted WHERE is_synched=0 AND publication_status=:published_status ORDER BY deletion_date ASC LIMIT ".$max_deletion_documents);
        $results = $cmd->queryAll(true, array(':published_status'=>Document::PUBLISHED));
        
        $apiClient = new APIClient(Yii::app()->params['api_url']);
        
        foreach($results as $result)
        {
            if($apiClient->sendRequest('DELETE', '/api/deletedocument/'.$result['document_id'], null, Yii::app()->params['api_username'], Yii::app()->params['api_password']))
            {
                Yii::log('Del Document '.$result['document_id'].': '.$apiClient->getResponse()->getStatusCode(), 'info');
                Yii::log('Del Document '.$result['document_id'].': '.$apiClient->getResponse()->getBody(), 'info');                
                try{    
                    Yii::app()->db->createCommand("UPDATE documents_deleted SET is_synched = 1 WHERE id = :id")->execute(array(':id'=>$result['id']));
                }
                catch(Exception $e)
                {
                    Yii::log('Del Document '.$result['document_id'].': DB Error - '.$e->getMessage(), 'error');
                }
            }
            else
            {
                foreach($apiClient->getErrors() as $error)
                {
                    Yii::log('Del Document '.$result['document_id'].': '.$error, 'error');
                }
            }
        }
        
    }
    
}