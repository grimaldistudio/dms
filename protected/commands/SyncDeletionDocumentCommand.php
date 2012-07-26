<?php

require_once Yii::getPathOfAlias('vendors').DIRECTORY_SEPARATOR.'guzzle'.DIRECTORY_SEPARATOR.'guzzle.phar';

class SyncDeletionDocumentCommand extends CConsoleCommand{
    
    public function run($args)
    {
        $max_deletion_documents = Yii::app()->params['max_deletion_documents'];        
        $cmd = Yii::app()->db->createCommand("SELECT document_id FROM documents_deleted WHERE is_synched=0 AND publication_status=:published_status ORDER BY deletion_date ASC LIMIT ".$max_deletion_documents);
        $results = $cmd->queryAll(true, array(':published_status'=>Document::PUBLISHED));
        
        foreach($results as $result)
        {
            Yii::log('Synching document deleted:'.$result['document_id'], YII_TRACE_LEVEL);
        }
        
    }
    
}