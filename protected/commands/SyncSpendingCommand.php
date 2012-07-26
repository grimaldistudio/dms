<?php

require_once Yii::getPathOfAlias('vendors').DIRECTORY_SEPARATOR.'guzzle'.DIRECTORY_SEPARATOR.'guzzle.phar';

class SyncSpendingCommand extends CConsoleCommand{
    
    public function run($args)
    {
        $max_spendings = Yii::app()->params['max_spendings'];
        $cmd = Yii::app()->db->createCommand("SELECT * FROM spendings WHERE is_dirty=1 AND (publication_requested=1 OR publication_status=:published_status) ORDER BY last_updated ASC LIMIT ".$max_spendings);
        $results = $cmd->queryAll(true, array(':published_status'=>Spending::PUBLISHED));
        $attributes_to_sync = array('id', 
                                    'title', 
                                    'amount', 
                                    'spending_date', 
                                    'employee', 
                                    'office', 
                                    'attribution_norm', 
                                    'attribution_mod', 
                                    'receiver', 
                                    'description', 
                                    'status', 
                                    'cv_name', 
                                    'capitulate_name', 
                                    'contract_name', 
                                    'project_name');
        
        $post_data = array();
        foreach($results as $result)
        {
            Yii::log('Syncrhonizzazione spesa :'.$result['id'], YII_TRACE_LEVEL);
            $post_data[] = array_intersect_key($result, $attributes_to_sync);
        }
        echo CJSON::encode($post_data);
        
    }
    
}