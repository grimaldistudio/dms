<?php

require_once Yii::getPathOfAlias('vendors').DIRECTORY_SEPARATOR.'guzzle'.DIRECTORY_SEPARATOR.'guzzle.phar';

class SyncDocumentCommand extends CConsoleCommand{
    
    public function run($args)
    {
        $max_documents = Yii::app()->params['max_documents'];
        $cmd = Yii::app()->db->createCommand("SELECT * FROM documents WHERE main_document_type = :public_document_type AND is_dirty=1 AND (publication_requested=1 OR publication_status= :published_status) ORDER BY last_updated ASC LIMIT ".$max_documents);
        $results = $cmd->queryAll(true, array(':public_document_type'=> Document::OUTGOING, ':published_status'=>Document::PUBLISHED));
        $attributes_to_sync = array('id' => '', 
                                    'name' => '', 
                                    'identifier' => '', 
                                    'description' => '', 
                                    'status' => '', 
                                    'publication_date_from' => '',
                                    'publication_date_to' => '',
                                    'act_date' => '',
                                    'act_number' => '',
                                    'entity' => '',
                                    'proposer_service' => '',
                                    'document_type' => '',
                                    'publication_requested' => ''
                                    );
        
        $apiClient = new APIClient(Yii::app()->params['api_url']);
        $model = new Document();
        foreach($results as $result)
        {
            $data = array_intersect_key($result, $attributes_to_sync);
            $time = strtotime($result['date_created']);
            $data['document_type_name'] = $model->getTypeDesc($data['document_type']);
            $data['relative_path'] = 'saved'.DIRECTORY_SEPARATOR.date('Y', $time).DIRECTORY_SEPARATOR.date('m', $time).DIRECTORY_SEPARATOR.date('d', $time);
            if($apiClient->sendRequest('POST', '/api/syncdocument', $data, Yii::app()->params['api_username'], Yii::app()->params['api_password']))
            {
                Yii::log('Spending '.$result['id'].': '.$apiClient->getResponse()->getStatusCode(), 'info');
                Yii::log('Spending '.$result['id'].': '.$apiClient->getResponse()->getBody(), 'info');                
                try{
                    Yii::app()->db->createCommand("UPDATE documents SET is_dirty = 0, publication_status = :published_status WHERE id=:id")->execute(array(':published_status'=>Document::PUBLISHED, ':id'=>$result['id']));
                }
                catch(Exception $e)
                {
                    Yii::log('Spending '.$result['id'].': DB Error - '.$e->getMessage(), 'error');                    
                }
            }
            else
            {
                foreach($apiClient->getErrors() as $error)
                {
                    Yii::log('Spending '.$result['id'].': '.$error, 'error');
                }
            }
        }
        
        
    }
    
}