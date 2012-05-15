<?php

class Document extends CActiveRecord
{

    const DISABLED_STATUS = 0;
    const ACTIVE_STATUS = 1;
    
    const LOW_PRIORITY = 0;
    const MEDIUM_PRIORITY = 1;
    const HIGH_PRIORITY = 2;
    const VERY_HIGH_PRIORITY = 3;
    
    public $tagsname = null;
    public $sendername = null;
    public $senderaddress = null;
    
    public $tags_array = array();
    
    public $tmp_path;
    public $document_manager = null;
    public $change_description;
    
    public $relative_path = null;

    
    public $date_received_from;
    public $date_received_to;

    public function __construct($scenario = 'insert') {
        parent::__construct($scenario);
        if($scenario == 'protocol' || $scenario == 'archive')
        {
            $this->date_received = date('d/m/Y');
        }
    }
    
    /**
    * Returns the static model of the specified AR class.
    * @return CActiveRecord the static model class
    */
    public static function model($className=__CLASS__)
    {
        return CActiveRecord::model($className);
    }

    /**
    * @return string the associated database table name
    */
    public function tableName()
    {
        return 'documents';
    }

    /**
    * @return array validation rules for model attributes.
    */
    public function rules()
    {
        return array(
            array('name,subject,sender_id,description,sendername,tagsname,priority,date_received,document_type', 'required', 'on'=>'protocol,archive,admin'),
            array('description,tagsname,document_type,priority,change_description,revision', 'required', 'on'=>'update,admin'),
            array('revision', 'required', 'on'=>'update'),            
            array('identifier', 'required', 'on'=>'protocol'),
            array('identifier', 'unique', 'on'=>'protocol'),
            array('senderaddress', 'safe', 'on'=>'protocol,archive,update,admin'),
            array('date_received', 'date', 'format'=>'dd/MM/yyyy', 'timestampAttribute'=>'date_received', 'on'=>'protocol,archive'),
            array('description', 'length', 'max'=>2048, 'on'=>'protocol,archive,update,admin'),
            array('description','filter','filter'=>array($obj=new CHtmlPurifier(),'purify'), 'on'=>'protocol,archive,update,admin'),
            array('identifier,name,date_received_from,date_received_to', 'safe', 'on'=>'my,disabled,created'),
            array('date_received_from', 'date', 'format'=>'dd/MM/yyyy', 'timestampAttribute'=>'date_received_from', 'on'=>'my,created,disabled'),
            array('date_received_to', 'date', 'format'=>'dd/MM/yyyy', 'timestampAttribute'=>'date_received_to', 'on'=>'my,created,disabled')
        );
    }

    /**
    * @return array relational rules.
    */
    public function relations()
    {
        return array(
            'tags' => array(self::MANY_MANY, 'Tag', 'tags_documents(document_id, tag_id)' ),
            'history' => array(self::HAS_MANY, 'DocumentHistory', 'document_id'),
            'rights' => array(self::HAS_MANY, 'DocumentRight', 'document_id'),
            'comments' => array(self::HAS_MANY, 'Comment', 'document_id'),
            'creator' => array(self::BELONGS_TO, 'User', 'creator_id'),
            'last_updater' => array(self::BELONGS_TO, 'User', 'last_updater_id'),            
            'sender' => array(self::BELONGS_TO, 'Sender', 'sender_id'),
            'folders' => array(self::MANY_MANY, 'Folder', 'documents_folders(document_id, folder_id)'),
            'notifications' => array(self::HAS_MANY, 'Notification', 'document_id')
        );
    }

    /**
    * @return array customized attribute labels (name=>label)
    */
    public function attributeLabels()
    {
        return array(
            'id' => 'Id',
            'name' => 'Nome',
            'description' => 'Descrizione',
            'subject' => 'Oggetto',
            'date_received'=> 'Data di ricezione',
            'date_created' => 'Data di creazione',
            'last_updated' => 'Ultimo aggiornamento',
            'status' => 'Stato',
            'identifier' => 'Numero Protocollo',
            'document_type' => 'Tipo Documento',
            'priority' => 'Priorità',
            'creator_id' => 'Creatore',
            'sender_id' => 'Mittente',
            'revision' => 'Revisione',
            'tagsname'=>'Tags',
            'sendername'=>'Mittente',
            'comments_enabled' => 'Commenti abilitati',
            'document_size' => 'Dimensione',
            'num_pages' => 'Numero Pagine',
            'change_description' => 'Descrizione modifiche',
            'last_updater_id' => 'Ultimo aggiornamento di',
            'date_received_from' => 'Ricevuto a partire da',
            'date_received_to' => 'Ricevuto fino a'
        );
    }

    function __toString()
    {
        return $this->name;
    }
    
    public function afterValidate()
    {
        $tags = explode(",", $this->tagsname);
        foreach($tags as $tag)
        {
            $tag = trim($tag);
            if(strlen($tag)>2)
            {
                $this->tags_array[] = $tag;
            }
        }
    }
    
    public function beforeSave()
    {
        if ($this->isNewRecord){
            if($this->status<=0)
                $this->status = Document::ACTIVE_STATUS;
            $this->date_created = date('Y-m-d H:i:s', time());
            $this->creator_id = Yii::app()->user->id;
        }
        else
        {
            $this->revision = $this->revision+1;
            $this->last_updater_id = Yii::app()->user->id;
        }
        
        $this->last_updated = new CDbExpression('CURRENT_TIMESTAMP');
        if(is_int($this->date_received))
        {
            $this->date_received = date('Y-m-d H:i:s', $this->date_received);
        }
        return parent::beforeSave();
    }

    public function protocolDocument()
    {
        // start transaction
        $t = Yii::app()->db->beginTransaction();
        if($this->save())
        {
            // save tags
            if($this->updateTags())
            {
                // move file to the proper folder
                if($this->protocolFile())
                {
                    // commit
                    $t->commit();
                    return true;
                }
            }
        }
        // rollback
        $t->rollback();
        return false;
    }
    
    public function archiveDocument()
    {
        // start transaction
        $t = Yii::app()->db->beginTransaction();
        if($this->save())
        {
            // save tags
            if($this->updateTags())
            {
                // move file to the proper folder
                if($this->archiveFile())
                {
                    // commit
                    $t->commit();
                    return true;
                }
            }
        }
        // rollback
        $t->rollback();
        return false;
    }
    
    public function updateDocument()
    {
        // start transaction
        $t = Yii::app()->db->beginTransaction();
        $this->revision = $this->revision+1;
        if($this->save())
        {
            // save tags
            if($this->updateTags())
            {
                if($this->saveHistory())
                {
                    // commit
                    $t->commit();
                    return true;
                }
            }
        }
        $t->rollBack();
        // rollback        
        return false;
    }
    
    private function updateTags()
    {
        $safe_tags = array();
        foreach($this->tags_array as $tag)
        {
            $safe_tags[] = Yii::app()->db->quoteValue($tag);
        }
        $safe_tags_str = implode(",", $safe_tags);

        $stm = Yii::app()->db->createCommand("DELETE FROM tags_documents WHERE document_id = :document_id");

        if($stm->execute(array(':document_id' => $this->id))===FALSE)
        {
            return FALSE;
        }

        $batch_inserts = array();
        foreach($safe_tags as $tag)
        {
            $batch_inserts[] = "(".$tag.")";
        }

        $stm = Yii::app()->db->createCommand("INSERT IGNORE INTO tags(name) VALUES ".implode(",", $batch_inserts));
        if($stm->execute()===FALSE)
        {
            return FALSE;
        }

        $stm = Yii::app()->db->createCommand("INSERT IGNORE INTO tags_documents(document_id, tag_id) SELECT :document_id, id FROM tags WHERE name IN (".$safe_tags_str.")");
        if($stm->execute(array(':document_id' => $this->id))===FALSE)
        {
            return FALSE;
        }	

        return TRUE;        
    }
    
    private function saveHistory()
    {
        $sql = "INSERT INTO document_history(user_id, document_id, description, revision, date_created) VALUES(:user_id, :document_id, :description, :revision, CURRENT_TIMESTAMP);";
        $cmd = Yii::app()->db->createCommand($sql);
        if($cmd->execute(array(':user_id'=>Yii::app()->user->id, ':document_id'=>$this->id, ':description'=>$this->change_description, ':revision'=>$this->revision)))
            return true;
        return false;
    }
    
    private function archiveFile()
    {
        if(file_exists($this->tmp_path))
        {
            $time = strtotime($this->date_created);
            $dst_path = Yii::getPathOfAlias('uploads').DIRECTORY_SEPARATOR.'archive'.DIRECTORY_SEPARATOR.date('Y', $time).DIRECTORY_SEPARATOR.date('m', $time).DIRECTORY_SEPARATOR.date('d', $time);
            if(file_exists($dst_path) || mkdir($dst_path, 0777, true))
            {
                if(@rename($this->tmp_path, $dst_path.DIRECTORY_SEPARATOR.'documento_'.$this->id.'.pdf'))
                {
                    $this->document_manager->deleteCacheFiles();
                    return true;
                }
                
            }
            return false;
        }
        return false;
    }
    
    private function protocolFile()
    {
        if(file_exists($this->tmp_path))
        {
            $time = strtotime($this->date_created);
            $dst_path = Yii::getPathOfAlias('uploads').DIRECTORY_SEPARATOR.'protocol'.DIRECTORY_SEPARATOR.date('Y', $time).DIRECTORY_SEPARATOR.date('m', $time).DIRECTORY_SEPARATOR.date('d', $time);
            if(file_exists($dst_path) || mkdir($dst_path, 0777, true))
            {
                if(rename($this->tmp_path, $dst_path.DIRECTORY_SEPARATOR.'documento_'.$this->id.'.pdf'))
                {
                    $this->document_manager->deleteCacheFiles();
                    return true;
                }
                
            }
            return false;
        }
        return false;        
    }
    
    public function disable()
    {
        $t = Yii::app()->db->beginTransaction();
        $this->status = self::DISABLED_STATUS;
        $this->change_description = "Documento rimosso dall'elenco";
        if($this->save())
        {
            if($this->saveHistory())
            {
                $t->commit();
                return true;
            }
        }
        $t->rollback();
        return false;
    }
    
    public function enable()
    {
        $t = Yii::app()->db->beginTransaction();
        $this->status = self::ACTIVE_STATUS;
        $this->change_description = "Documento ripristinato nell'elenco";
        if($this->save())
        {
            if($this->saveHistory())
            {
                $t->commit();
                return true;
            }
        }
        $t->rollback();
        return false;        
    }
    
    public function getStatusArray()
    {
        return array(
            '' => 'Seleziona',		
            self::DISABLED_STATUS => 'Disabilitato',
            self::ACTIVE_STATUS => 'Attivo',
        );
    }

    public function getStatusDesc()
    {
        $status_array = $this->getStatusArray();
        if(array_key_exists($this->status, $status_array))
            return $status_array[$this->status];
        return 'Non definito';
    }

    public function getPriorityOptions()
    {
        return array(
            self::LOW_PRIORITY => 'Bassa',
            self::MEDIUM_PRIORITY => 'Media',
            self::HIGH_PRIORITY => 'Alta',
            self::VERY_HIGH_PRIORITY => 'Molto Alta'
        );
    }
    
    public function getPriorityDesc()
    {
        if(array_key_exists($this->priority, $this->getPriorityOptions()))
        {
            $options = $this->getPriorityOptions();
            return $options[$this->priority];
        }
        return 'n/d';
    }
    
    public function getTypeOptions()
    {
        return array(
          0 => 'Tipo 1',
          1 => 'Tipo 2',
          2 => 'Tipo 3'  
        );
    }
    
    public function getTypeDesc()
    {
        if(array_key_exists($this->document_type, $this->getTypeOptions()))
        {
            $options = $this->getTypeOptions();
            return $options[$this->document_type];
        }
        return 'n/d';        
    }
    
    public function getRelativePath()
    {
        if(is_null($this->relative_path))
        {
            $time = strtotime($this->date_created);
            if($this->isArchived())
                $this->relative_path = 'archive'.DIRECTORY_SEPARATOR.date('Y', $time).DIRECTORY_SEPARATOR.date('m', $time).DIRECTORY_SEPARATOR.date('d', $time);        
            else
                $this->relative_path = 'protocol'.DIRECTORY_SEPARATOR.date('Y', $time).DIRECTORY_SEPARATOR.date('m', $time).DIRECTORY_SEPARATOR.date('d', $time); 
        }
        return $this->relative_path;
    }
    
    public function getDocumentName()
    {
        return 'documento_'.$this->id.'.pdf';
    }
    
    public function getPath()
    {
        return Yii::getPathOfAlias('uploads').DIRECTORY_SEPARATOR.$this->getRelativePath().DIRECTORY_SEPARATOR.$this->getDocumentName();
    }
    
    public function getCachePath()
    {
        return Yii::getPathOfAlias('uploads').DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.$this->getRelativePath();
    }
    
    public function isArchived()
    {
        return $this->identifier==null || $this->identifier=="";
    }
    
    public function isProtocolled()
    {
        return !$this->isArchived();
    }
    
    public function download($force_download = false)
    {
        $path = $this->getPath();
        if(file_exists($path)){
            if($force_download)
                header('Content-disposition: attachment; filename='.$this->getDocumentName());
            header('Content-type: application/pdf');
            readfile($path);
            Yii::app()->end();
        }
        else {
            throw new CHttpException(404, 'File non trovato');
        }
    }
    
    public function my()
    {
        $this->validate();
        
        $params = array();
        $sql_select = "SELECT d.id, d.name, d.identifier, d.subject, d.description, d.date_received, d.last_updated, u.firstname, u.lastname, u.email ";
        $sql_count = "SELECT COUNT(DISTINCT(d.id)) ";
        
        $sql_group = " GROUP BY d.id";
        if(Yii::app()->user->isAdmin())
        {
            $sql_from = " FROM documents d";
            $sql_join = " JOIN users u ON u.id = d.creator_id ";
            $sql_where = " WHERE 1=1 ";
        }
        else
        {
            $user_groups = Yii::app()->user->getGroups();
            $user_groups_ids = array_keys($user_groups);
            if(count($user_groups_ids)==0)
            {
                $user_groups_ids = array(0);
            }
            $sql_select .= ", MAX(dr.right) as max_right";
            $sql_from = " FROM documents_rights dr ";
            $sql_join = " JOIN documents d ON dr.document_id = d.id ";
            $sql_join .= " LEFT JOIN users u ON u.id = dr.user_id ";
            $sql_where = " WHERE (dr.user_id = :user_id 
                            OR dr.group_id IN (".implode(",",$user_groups_ids).") )
            ";
            $params[':user_id'] = Yii::app()->user->id;

        }
        
        $sql_where .= " AND d.status = :active_status";
        
        $params[':active_status'] = Document::ACTIVE_STATUS;
        
        if($this->identifier)
        {
            $sql_where .= " AND d.identifier = :identifier";
            $params[':identifier'] = $this->identifier;
        }
        
        if($this->name)
        {
            $sql_where .= " AND d.name LIKE :name";
            $params[':name'] = '%'.$this->name.'%';
        }
        
        if(!$this->hasErrors('date_received_from') && $this->date_received_from)
        {
            $sql_where .= " AND d.date_received >= :date_received_from";
            $params[':date_received_from'] = date('Y-m-d', $this->date_received_from);
        }
        
        if(!$this->hasErrors('date_received_to') && $this->date_received_to)
        {
            $sql_where .= " AND d.date_received <= :date_received_to";
            $params[':date_received_to'] = date('Y-m-d', $this->date_received_to);            
        }
        
        $sql = $sql_select . $sql_from . $sql_join . $sql_where . $sql_group;
        $qcount = $sql_count . $sql_from . $sql_join . $sql_where;

        $count = Yii::app()->db->createCommand($qcount)->queryScalar($params);
        
        return new CSqlDataProvider(
            $sql,
            array(
                'totalItemCount'=>$count,
                    'sort' => array(
                        'attributes' => array('date_received', 'name', 'identifier'),
                        'defaultOrder' => 'd.date_received DESC'
                ),
                'pagination' => array(
                    'pageSize' => 10
                ),
                'params'=>$params
            )
        );

    }
    
    public function disabled()
    {
        $this->validate();
        
        $params = array();
        $sql_select = "SELECT d.id, d.name, d.identifier, d.subject, d.description, d.date_received, d.last_updated, u.firstname, u.lastname, u.email ";
        $sql_count = "SELECT COUNT(DISTINCT(d.id)) ";
        
        $sql_group = " GROUP BY d.id";
        if(Yii::app()->user->isAdmin())
        {
            $sql_from = " FROM documents d";
            $sql_join = " JOIN users u ON u.id=d.creator_id ";
            $sql_where = " WHERE 1=1 ";
        }
        else
        {
            $user_groups = Yii::app()->user->getGroups();
            $user_groups_ids = array_keys($user_groups);
            if(count($user_groups_ids)==0)
            {
                $user_groups_ids = array(0);
            }            
            $sql_from = " FROM documents dr ";
            $sql_join = " JOIN users u ON u.id=dr.user_id ";
            $sql_join .= " LEFT JOIN documents_rights d ON dr.document_id = d.id ";
            $sql_where = " WHERE (dr.user_id = :user_id 
                            OR dr.group_id IN (".implode(",",$user_groups_ids).") AND dr.right = :admin_right)
                            OR d.creator_id = :user_id
            ";
            
            $params[':user_id'] = Yii::app()->user->id;
            $params[':admin_right'] = DocumentRight::ADMIN;
        }
        
        $sql_where .= " AND d.status = :inactive_status";
        
        $params[':inactive_status'] = Document::DISABLED_STATUS;
        
        if($this->identifier)
        {
            $sql_where .= " AND d.identifier = :identifier";
            $params[':identifier'] = $this->identifier;
        }
        
        if($this->name)
        {
            $sql_where .= " AND d.name LIKE :name";
            $params[':name'] = '%'.$this->name.'%';
        }
        
        if(!$this->hasErrors('date_received_from') && $this->date_received_from)
        {
            $sql_where .= " AND d.date_received >= :date_received_from";
            $params[':date_received_from'] = date('Y-m-d', $this->date_received_from);
        }
        
        if(!$this->hasErrors('date_received_to') && $this->date_received_to)
        {
            $sql_where .= " AND d.date_received <= :date_received_to";
            $params[':date_received_to'] = date('Y-m-d', $this->date_received_to);            
        }
        
        $sql = $sql_select . $sql_from . $sql_join . $sql_where . $sql_group;
        $qcount = $sql_count . $sql_from . $sql_join . $sql_where;

        $count = Yii::app()->db->createCommand($qcount)->queryScalar($params);
        
        return new CSqlDataProvider(
            $sql,
            array(
                'totalItemCount'=>$count,
                    'sort' => array(
                        'attributes' => array('date_received', 'name', 'identifier'),
                        'defaultOrder' => 'd.date_received DESC'
                ),
                'pagination' => array(
                    'pageSize' => 10
                ),
                'params'=>$params
            )
        );

    }    
    
    public function created()
    {
        $this->validate();
                
        $params = array();
        $sql_select = "SELECT d.id, d.name, d.identifier, d.subject, d.description, d.date_received, d.last_updated, d.last_updater_id ";
        $sql_count = "SELECT COUNT(d.id) ";
        
        $sql_from = " FROM documents d";
        $sql_where = " WHERE creator_id = :user_id AND status = :active_status";
        
        $params[':user_id'] = Yii::app()->user->id;
        $params[':active_status'] = Document::ACTIVE_STATUS;
        
        if($this->identifier)
        {
            $sql_where .= " AND d.identifier = :identifier";
            $params[':identifier'] = $this->identifier;
        }
        
        if($this->name)
        {
            $sql_where .= " AND d.name LIKE :name";
            $params[':name'] = '%'.$this->name.'%';
        }
        
        if(!$this->hasErrors('date_received_from') && $this->date_received_from)
        {
            $sql_where .= " AND d.date_received >= :date_received_from";
            $params[':date_received_from'] = date('Y-m-d', $this->date_received_from);
        }
        
        if(!$this->hasErrors('date_received_to') && $this->date_received_to)
        {
            $sql_where .= " AND d.date_received <= :date_received_to";
            $params[':date_received_to'] = date('Y-m-d', $this->date_received_to);            
        }
        
        $sql = $sql_select . $sql_from . $sql_where;
        $qcount = $sql_count . $sql_from . $sql_where;

        $count = Yii::app()->db->createCommand($qcount)->queryScalar($params);
        
        return new CSqlDataProvider(
            $sql,
            array(
                'totalItemCount'=>$count,
                    'sort' => array(
                        'attributes' => array('date_received', 'name', 'identifier'),
                        'defaultOrder' => 'd.date_received DESC'
                ),
                'pagination' => array(
                    'pageSize' => 10
                ),
                'params'=>$params
            )
        );

    }    
    
    public function loadTagsArray()
    {
        $sql = "SELECT t.name FROM tags_documents td JOIN tags t ON t.id=td.tag_id WHERE td.document_id = :document_id";
        $cmd = Yii::app()->db->createCommand($sql);
        $rows = $cmd->queryAll(true, array(':document_id'=>$this->id));
        foreach($rows as $row)
            $this->tags_array[] = $row['name'];
        
        $this->tagsname = implode(',', $this->tags_array);
    }
    
    public function loadSenderData()
    {
        if($this->sendername==null)
        {
            $this->sendername = $this->sender->name;
            $this->senderaddress = $this->sender->getFullAddress();
        }
    }
    
    public function deleteDocument()
    {
        $name = $this->getDocumentName();
        $path = $this->getPath();
        $cache_path = $this->getCachePath();
        $db = Yii::app()->db->beginTransaction();
        
        if($this->delete())
        {
            if(@unlink($path))
            {
                @exec('rm -f '.$cache_path.DIRECTORY_SEPARATOR.$name.'_*.jpg');
                $db->commit();
                return true;

            }
        }
        $db->rollback();
        return false;
    }
    
    public function getTitle()
    {
        if($this->identifier)
        {
            return '#'.$this->identifier;
        }
        else
        {
            return $this->name;
        }
    }
}