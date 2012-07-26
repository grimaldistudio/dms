<?php

require_once Yii::getPathOfAlias('vendors').DIRECTORY_SEPARATOR.'guzzle'.DIRECTORY_SEPARATOR.'guzzle.phar';

class SyncDocumentCommand extends CConsoleCommand{
    
    public function run($args)
    {
        $max_documents = Yii::app()->params['max_documents'];
        $cmd = Yii::app()->db->createCommand("SELECT * FROM documents WHERE main_document_type = :public_document_type AND is_dirty=1 AND (publication_requested=1 OR publication_status= :published_status) ORDER BY last_updated ASC LIMIT ".$max_documents);
        $results = $cmd->queryAll(true, array(':public_document_type'=> Document::OUTGOING, ':published_status'=>Document::PUBLISHED));
        $attributes_to_sync = array('id', 
                                    'subject', 
                                    'identifier', 
                                    'description', 
                                    'status', 
                                    'publication_date_from',
                                    'publication_date_to',
                                    'act_date',
                                    'act_number',
                                    'relative_path',
                                    'entity',
                                    'proposer_service',
                                    'document_type'
                                    );
        
        $post_data = array();
        foreach($results as $result)
        {
            Yii::log('Syncrhonizzazione spesa :'.$result['id'], YII_TRACE_LEVEL);
            $post_data[] = array_intersect_key($result, $attributes_to_sync);
        }
        echo CJSON::encode($post_data);
        
    }
    
}