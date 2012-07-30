<?php

require_once Yii::getPathOfAlias('vendors').DIRECTORY_SEPARATOR.'guzzle'.DIRECTORY_SEPARATOR.'guzzle.phar';

class SyncDeletionSpendingCommand extends CConsoleCommand{
    
    public function run($args)
    {
        $max_deletion_spendings = Yii::app()->params['max_deletion_spendings'];
        $cmd = Yii::app()->db->createCommand("SELECT id, spending_id FROM spendings_deleted WHERE is_synched=0 AND publication_status=:published_status ORDER BY deletion_date ASC LIMIT ".$max_deletion_spendings);
        $results = $cmd->queryAll(true, array(':published_status'=>Spending::PUBLISHED));
        
        $apiClient = new APIClient(Yii::app()->params['api_url']);
        
        foreach($results as $result)
        {
            if($apiClient->sendRequest('DELETE', '/api/deletespending/'.$result['spending_id'], null, Yii::app()->params['api_username'], Yii::app()->params['api_password']))
            {
                Yii::log('Del Spending '.$result['spending_id'].': '.$apiClient->getResponse()->getStatusCode(), 'info');
                Yii::log('Del Spending '.$result['spending_id'].': '.$apiClient->getResponse()->getBody(), 'info');                
                try
                {
                    Yii::app()->db->createCommand("UPDATE spendings_deleted SET is_synched = 1 WHERE id = :id")->execut(array(':id'=>$result['id']));
                }
                catch(Exception $e)
                {
                    Yii::log('Del Spending '.$result['spending_id'].': DB Error - '.$e->getMessage(), 'error');
                }
            }
            else
            {
                foreach($apiClient->getErrors() as $error)
                {
                    Yii::log('Del Spending '.$result['spending_id'].': '.$error, 'error');
                }
            }
        }
        
        
    }
    
}