<?php

require_once Yii::getPathOfAlias('vendors').DIRECTORY_SEPARATOR.'guzzle'.DIRECTORY_SEPARATOR.'guzzle.phar';

class SyncDeletionSpendingCommand extends CConsoleCommand{
    
    public function run($args)
    {
        $max_deletion_spendings = Yii::app()->params['max_deletion_spendings'];
        $cmd = Yii::app()->db->createCommand("SELECT spending_id FROM spendings_deleted WHERE is_synched=0 AND publication_status=:published_status ORDER BY deletion_date ASC LIMIT ".$max_deletion_spendings);
        $results = $cmd->queryAll(true, array(':published_status'=>Spending::PUBLISHED));
        foreach($results as $result)
        {
            Yii::log('Synching spending deleted:'.$result['spending_id'], YII_TRACE_LEVEL);
        }
        
    }
    
}