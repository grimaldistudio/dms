<?php

class DocumentSearchForm extends CFormModel{
    
    public $tags = null;
    public $tags_array = array();
    public $identifier = null;
    private $dataProvider = null;
    
    public $date_from = null;
    public $date_to = null;
    public $date_from_ts = null;
    public $date_to_ts = null;
    
    public function rules()
    {
        return array(
            array('identifier', 'required', 'on'=>'identifier'),
            array('tags', 'required', 'on'=>'tags'), 
            array('date_from,date_to', 'safe', 'on'=>'date'),
            array('date_from', 'date', 'format'=>'dd/MM/yyyy', 'timestampAttribute'=>'date_from_ts', 'on'=>'date'),
            array('date_to', 'date', 'format'=>'dd/MM/yyyy', 'timestampAttribute'=>'date_to_ts', 'on'=>'date')            
        );
    }
    
    public function getSelectedTags()
    {
        $this->buildTagsArray();
        if(count($this->tags_array)>0)
        {
            $sql = "SELECT id, name FROM tags WHERE id IN (".implode(",", $this->tags_array).")";
            $rows = Yii::app()->db->createCommand($sql)->queryAll(true);
            return $rows;
        }
        return array();
    }
    
    public function hasResults()
    {
        if($this->dataProvider)
            return $this->dataProvider->getItemCount()>0;
        return false;
    }
    
    public function hasDataProvider()
    {
        return $this->dataProvider!==null;
    }

    public function getDataProvider()
    {
        return $this->dataProvider;
    }
    
    public function getFirst()
    {
        $data = $this->dataProvider->getData();
        return $data[0];
    }
    
    public function getData()
    {
        return $this->dataProvider->getData();
    }
    
    public function getResultsCount()
    {
        return $this->dataProvider->getItemCount();
    }
    
    public function search($doc_type = Document::INBOX)
    {
        if($this->dataProvider===null)
        {

            if($this->scenario == 'identifier')
            {
                $params = array(':identifier'=>$this->identifier, ':main_document_type'=>$doc_type);                
                $count = Yii::app()->db->createCommand("SELECT count(d.id) FROM documents d WHERE d.status = 1 AND d.identifier = :identifier")->queryScalar($params);

                $sql = "SELECT d.id, d.name, d.identifier, d.description, d.document_type, d.date_received, d.last_updated, d.publication_date_from, d.publication_date_to, d.publication_requested, d.publication_status,  FROM documents d
                                                            WHERE d.status = 1
                                                                AND d.identifier = :identifier  
                                                                AND d.main_document_type = :main_document_type
                ";
            }
            elseif($this->scenario == 'tags') // tags scenario
            {
                $this->buildTagsArray();
                $num_tags = count($this->tags_array);
                $params = array(':main_document_type'=>$doc_type);
                $sql_select = "SELECT i2t" . ($num_tags - 1) . ".document_id as id, d.identifier, d.name, d.description, d.document_type, d.date_received, d.publication_date_from, d.publication_date_to, d.publication_requested, d.publication_status, d.last_updated, u.firstname, u.lastname, u.email ";
                $sql_count_select = "SELECT count(DISTINCT(i2t" . ($num_tags - 1) . ".document_id)) ";
                $sql_from = " FROM ";
                $sql_where = " WHERE ";
                $sql_joins = "";
                $sql_group = " GROUP BY i2t" . ($num_tags - 1) . ".document_id ";
                foreach($this->tags_array as $i=>$tag)
                {
                    $params[':tag'.$i] = $tag;
                    
                    if ($i==0) {
                        $sql_from .= " tags t0 ";
                        $sql_where .= " t0.id = :tag0 ";
                        $sql_joins .= " INNER JOIN tags_documents i2t0 ON t0.id = i2t0.tag_id ";
                    }
                    else {
                        $sql_from .= " CROSS JOIN tags t" . $i;
                        $sql_where .= " AND t" . $i . ".id = :tag".$i." ";
                        $sql_joins .= " INNER JOIN tags_documents i2t" . $i . " ON i2t" . ($i - 1) . ".document_id = i2t" . $i . ".document_id " .
                                            " AND i2t" . $i . ".tag_id = t" . $i . ".id ";

                    }
                }
                
                $sql_joins .= " JOIN documents d ON d.id = i2t". ($num_tags - 1) .".document_id ";
                $sql_joins .= " JOIN users u ON u.id=d.creator_id ";

                $sql_where .= " AND d.status = :active_status ";
                $sql_where .= " AND d.main_document_type = :main_document_type ";
                
                $params[':active_status'] = Document::ACTIVE_STATUS;
                
                $sql = $sql_select . $sql_from . $sql_joins . $sql_where . $sql_group;
                $count_sql = $sql_count_select . $sql_from . $sql_joins . $sql_where;
               
                $count = Yii::app()->db->createCommand(
                            $count_sql
                        )->queryScalar($params);
                
            }
            else
            {
                $params = array(':main_document_type'=>$doc_type);
                $sql_count = "SELECT count(d.id) FROM documents d WHERE d.status = 1 ";
                $sql = "SELECT d.id, d.name, d.identifier, d.description, d.document_type, d.date_received, d.publication_date_from, d.publication_date_to, d.publication_requested, d.publication_status, d.last_updated, u.firstname, u.lastname, u.email 
                                                            FROM documents d
                                                            JOIN users u ON u.id = d.creator_id
                                                            WHERE d.status = 1
                                                            AND d.main_document_type = :main_document_type";
                
                $date_field = "date_received";
                if($doc_type==Document::INTERNAL_USE_TYPE)
                    $date_field = "d.date_created";
                elseif($doc_type==Document::OUTGOING)
                    $date_field = "publication_date_from";
                
                if($this->date_from_ts)
                {
                    $sql_count .= " AND ".$date_field." >=:date_received_from ";
                    $sql .= " AND ".$date_field." >=:date_received_from ";
                    $params[':date_received_from'] = date('Y-m-d', $this->date_from_ts);                    
                }
                
                $date_field = "date_received";
                if($doc_type==Document::INTERNAL_USE_TYPE)
                    $date_field = "d.date_created";
                elseif($doc_type==Document::OUTGOING)
                    $date_field = "publication_date_to";                

                if($this->date_to_ts)
                {
                    $sql_count .= " AND ".$date_field." <=:date_received_to ";
                    $sql .= " AND ".$date_field." <=:date_received_to ";
                    $params[':date_received_to'] = date('Y-m-d', $this->date_to_ts);
                }
                
                $count = Yii::app()->db->createCommand($sql_count)->queryScalar($params);
            }

            $date_field = "date_received";
            if($doc_type==Document::INTERNAL_USE_TYPE)
                $date_field = "d.date_created";
            elseif($doc_type==Document::OUTGOING)
                $date_field = "publication_date_from";                
                
            $this->dataProvider = new CSqlDataProvider(
                $sql,
                array(
                    'totalItemCount'=>$count,
                    'sort' => array(
                        'attributes' => array('identifier','name',$date_field,'last_updated'),
                        'defaultOrder' => $date_field.' DESC' 
                    ),
                    'pagination' => array(
                        'pageSize' => 10
                    ),
                    'params'=>$params
                )
            );

        }
        
        return $this->dataProvider;
    }
    
    protected function buildTagsArray()
    {
        $tags_tokens = explode(",",$this->tags);
        foreach($tags_tokens as $tag_token)
        {
            $this->tags_array[] = intval($tag_token);
            if(count($this->tags_array)>=3)
                break;
        }
    }
}
?>