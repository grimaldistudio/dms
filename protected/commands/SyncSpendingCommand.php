<?php

require_once Yii::getPathOfAlias('vendors').DIRECTORY_SEPARATOR.'guzzle'.DIRECTORY_SEPARATOR.'guzzle.phar';

class SyncSpendingCommand extends CConsoleCommand{
    
    public function run($args)
    {
        $max_spendings = Yii::app()->params['max_spendings'];
        $cmd = Yii::app()->db->createCommand("SELECT * FROM spendings WHERE is_dirty=1 AND (publication_requested=1 OR publication_status=:published_status) ORDER BY last_updated ASC LIMIT ".$max_spendings);
        $results = $cmd->queryAll(true, array(':published_status'=>Spending::PUBLISHED));
        $attributes_to_sync = array('id' => '', 
                                    'title' => '', 
                                    'amount' => '', 
                                    'spending_date' => '', 
                                    'employee' => '', 
                                    'office' => '', 
                                    'attribution_norm' => '', 
                                    'attribution_mod' => '', 
                                    'receiver' => '', 
                                    'description' => '', 
                                    'status' => '', 
                                    'cv_name' => '', 
                                    'capitulate_name' => '', 
                                    'contract_name' => '', 
                                    'project_name' => '',
                                    'publication_requested' => '');
        
        $apiClient = new APIClient(Yii::app()->params['api_url']);
        
        foreach($results as $result)
        {
            $data = array_intersect_key($result, $attributes_to_sync);
            
            if($apiClient->sendRequest('POST', '/api/syncspending', $data, Yii::app()->params['api_username'], Yii::app()->params['api_password']))
            {
                Yii::log('Spending '.$result['id'].': '.$apiClient->getResponse()->getStatusCode(), 'info');
                Yii::log('Spending '.$result['id'].': '.$apiClient->getResponse()->getBody(), 'info');
                try
                {
                    Yii::app()->db->createCommand('UPDATE spendings SET is_dirty = 0, publication_status = :published_status WHERE id = :id')->execute(array(':published_status'=>Spending::PUBLISHED, 'id'=>$result['id']));
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