<?php

class Document extends CActiveRecord
{

    const DISABLED_STATUS = 0;
    const ACTIVE_STATUS = 1;
    
    const LOW_PRIORITY = 0;
    const MEDIUM_PRIORITY = 1;
    const HIGH_PRIORITY = 2;
    const VERY_HIGH_PRIORITY = 3;

    const PUBLISHED = 1;
    const NOT_PUBLISHED = 0;
    
    const INTERNAL_USE_TYPE = 0;
    const INBOX = 3;
    const OUTGOING = 5;
    
    public $tagsname = null;
    public $sendername = null;
    public $senderaddress = null;
    
    public $tags_array = array();
    
    public $publication_requested;
    
    public $tmp_path;
    public $document_manager = null;
    public $change_description;
    
    public $relative_path = null;

    
    public $date_from;
    public $date_to;

    public function __construct($scenario = 'insert') {
        parent::__construct($scenario);
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
            array('name,identifier,sender_id,description,sendername,tagsname,priority,date_received', 'required', 'on'=>'protocol'),
            array('name,description,tagsname', 'required', 'on'=>'archive'),
            array('name,description,tagsname,document_type', 'required', 'on'=>'publish'),
            array('is_inbound', 'safe', 'on'=>'protocol,protocol_admin'),
            array('is_visible_to_all', 'safe', 'on'=>'protocol,protocol_admin,archive,archive_admin,publish,publish_admin'),
            array('is_visible_to_all', 'default', 'setOnEmpty'=>true, 'value'=>0, 'on'=>'protocol,protocol_admin,archive,archive_admin,publish,publish_admin'),            
            array('publication_number', 'unique', 'on'=>'publish,publish_admin'),
            array('publication_requested,sync_file', 'safe', 'on'=>'publish,publish_admin,publish_update'),
            array('publication_requested', 'default', 'setOnEmpty'=>true, 'value'=>0, 'on'=>'publish,publish_admin,publish_update'),            
            array('sync_file', 'default', 'setOnEmpty'=>true, 'value'=>0, 'on'=>'publish,publish_admin'),  
            array('is_inbound', 'default', 'setOnEmpty'=>true, 'value'=>0, 'on'=>'protocol,protocol_admin'),                                              
            array('entity,proposer_service,publication_date_from,publication_date_to,act_number,act_date', 'safe', 'on'=>'publish,publish_update,publish_admin'),
            array('description,tagsname,priority,sender_id,sendername', 'required', 'on'=>'protocol_update,protocol_admin'),
            array('description,tagsname', 'required', 'on'=>'publish_admin,publish_update,archive_update,archive_admin'),
            array('name', 'required', 'on'=>'protocol_admin,archive_admin,publish_admin'),
            array('change_description,revision', 'required', 'on'=>'protocol_update,protocol_admin,archive_update,archive_admin,publish_update,publish_admin'),            
            array('identifier', 'safe', 'on'=>'publish,publish_admin'),
            array('identifier,date_received', 'required', 'on' => 'protocol_admin'),
           // array('identifier', 'unique', 'on'=>'protocol,publish,protocol_admin,publish_admin'),
            array('senderaddress', 'safe', 'on'=>'protocol,protocol_update,protocol_admin'),
            array('date_received', 'date', 'format'=>'dd/MM/yyyy', 'timestampAttribute'=>'date_received', 'on'=>'protocol,protocol_admin'),
            array('act_date', 'date', 'format'=>'dd/MM/yyyy', 'timestampAttribute'=>'act_date', 'allowEmpty' =>true, 'on'=>'publish,publish_update,publish_admin'),          
            array('publication_date_from', 'date', 'format'=>'dd/MM/yyyy', 'timestampAttribute'=>'publication_date_from', 'allowEmpty' =>true, 'on'=>'publish,publish_update,publish_admin'),          
            array('publication_date_to', 'date', 'format'=>'dd/MM/yyyy', 'timestampAttribute'=>'publication_date_to', 'allowEmpty' =>true, 'on'=>'publish,publish_update,publish_admin'),          
            array('date_received,act_date,publication_date_from,publication_date_to', 'default', 'setOnEmpty'=>true, 'value'=>new CDbExpression('NULL')),
            array('description', 'length', 'max'=>2048, 'on'=>'protocol,archive,publish,protocol_update,protocol_admin,publish_update,publish_admin,archive_update,archive_admin'),
            array('description','filter','filter'=>array($obj=new CHtmlPurifier(),'purify'), 'on'=>'protocol,archive,publish,protocol_update,protocol_admin,publish_update,publish_admin,archive_update,archive_admin'),
            array('identifier,name,date_from,date_to,main_document_type', 'safe', 'on'=>'my,disabled,created,public'),
            array('date_from', 'date', 'format'=>'dd/MM/yyyy', 'timestampAttribute'=>'date_from', 'on'=>'my,created,disabled,public'),
            array('date_to', 'date', 'format'=>'dd/MM/yyyy', 'timestampAttribute'=>'date_to', 'on'=>'my,created,disabled,public'),
            array('identifier,name, main_document_type', 'safe', 'on'=>'dashboard')
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
            'name' => 'Titolo',
            'description' => 'Descrizione',
            'is_inbound' => 'Tipologia',
            'is_visible_to_all' => 'Visibile a tutti',
            'sync_file' => 'Pubblicazione file su albo',
            'publication_number' => 'Numero di pubblicazione',
            'date_received'=> 'Data di invio/ricezione',
            'date_created' => 'Data di creazione',
            'last_updated' => 'Ultimo aggiornamento',
            'status' => 'Stato',
            'identifier' => 'Numero Protocollo',
            'main_document_type' => 'Tipo di documento',
            'document_type' => 'Tipo di documento pubblico',
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
            'date_from' => 'Da',
            'date_to' => 'A',
            'entity' => 'Ente',
            'proposer_service' => 'Servizio proponente',
            'act_number' => 'Numero atto',
            'act_date' => 'Data atto',
            'publication_date_from' => 'Data inizio pubblicazione',
            'publication_date_to' => 'Data fine pubblicazione',
            'publication_status' => 'Pubblicazione albo',
            'publication_requested' => 'Pubblicazione su albo',
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

        if(is_int($this->act_date))
            $this->act_date = date('Y-m-d H:i:s', $this->act_date);
        if(is_int($this->publication_date_from))
            $this->publication_date_from = date('Y-m-d H:i:s', $this->publication_date_from);
        if(is_int($this->publication_date_to))
            $this->publication_date_to = date('Y-m-d H:i:s', $this->publication_date_to);            
        if(is_int($this->date_received))
            $this->date_received = date('Y-m-d H:i:s', $this->date_received);
        return parent::beforeSave();
    }

    public function protocolDocument()
    {
        // start transaction
        $t = Yii::app()->db->beginTransaction();
        $this->is_dirty = 1;        
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
        $this->is_dirty = 1;        
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
        $this->is_dirty = 1;
        if($this->save())
        {
            // save tags
            if($this->updateTags())
            {
                if($this->saveHistory())
                {
                    // TODO
                    if(($this->document_type==self::OUTGOING && $this->syncFile()) || $this->document_type!=self::OUTGOING)
                    {                        
                        // commit
                        $t->commit();
                        return true;
                    }
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
            $dst_path = Yii::getPathOfAlias('uploads').DIRECTORY_SEPARATOR.'saved'.DIRECTORY_SEPARATOR.date('Y', $time).DIRECTORY_SEPARATOR.date('m', $time).DIRECTORY_SEPARATOR.date('d', $time);
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
            $dst_path = Yii::getPathOfAlias('uploads').DIRECTORY_SEPARATOR.'saved'.DIRECTORY_SEPARATOR.date('Y', $time).DIRECTORY_SEPARATOR.date('m', $time).DIRECTORY_SEPARATOR.date('d', $time);
            
            if(file_exists($dst_path) || mkdir($dst_path, 0777, true))
            {
                if(rename($this->tmp_path, $dst_path.DIRECTORY_SEPARATOR.'documento_'.$this->id.'.pdf'))
                {
                    $this->document_manager->deleteCacheFiles();
                    if(($this->publication_requested && $this->addPublishedFile()) || !$this->publication_requested)
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
        $this->is_dirty = 1;        
        if($this->save())
        {
            if($this->saveHistory())
            {
                if(($this->isSynched() && $this->removePublishedFile()) || !$this->isSynched())
                {
                    $t->commit();
                    return true;
                }
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
        $this->is_dirty = 1;        
        if($this->save())
        {
            if($this->saveHistory())
            {
                if(($this->isSynched() && $this->addPublishedFile()) || !$this->isSynched())
                {
                    $t->commit();
                    return true;
                }
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
    
    public function getPublicationStatusArray()
    {
        return array(
            '' => 'Seleziona',		
            self::NOT_PUBLISHED => 'Non pubblicato',
            self::PUBLISHED => 'Pubblicato'
        );
    }

    public function getPublicationStatusDesc($value = -1)
    {
        if($value<0)
            $value = $this->publication_status;
        
        $status_array = $this->getPublicationStatusArray();
        if(array_key_exists($value, $status_array))
            return $status_array[$value];
        return 'Non definito';
    }

    public function getProtocolDesc($value = -1)
    {
        if($value<0)
            $value = $this->is_inbound;
        
        if($value==0)
            return "In uscita";
        else
            return "In entrata";
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

    public function getMainTypeOptions()
    {
        return array(
           // self::INBOX => 'Posta in entrata/uscita',
            self::OUTGOING => 'Documenti pubblici',
           // self::INTERNAL_USE_TYPE => 'Archivio personale'
        );        
    }

    public function getMainTypeDesc()
    {
        if(array_key_exists($this->main_document_type, $this->getMainTypeOptions()))
        {
            $options = $this->getMainTypeOptions();
            return $options[$this->main_document_type];
        }
        return 'n/d';        
    }

    public function getTypeOptions()
    {
        return array(
            5 => 'Avviso di accertamento',
            10 => 'Bandi e avvisi',
            15 => 'Convocazioni',
            17 => 'Delibere di giunta',
            19 => 'Delibere di consiglio',
            20 => 'Determine',
            25 => 'Oggetti e valori ritrovati',
            30 => 'Ordinanze',
            35 => 'Pubblicazioni di matrimonio',
            40 => 'Pubblicazioni di altri enti',
            45 => 'Pubblicazioni varie'
        );
    }
    
    public function getTypeDesc($value = -1)
    {
        if($value<0)
            $value = $this->document_type;
        if(array_key_exists($value, $this->getTypeOptions()))
        {
            $options = $this->getTypeOptions();
            return $options[$value];
        }
        return 'n/d';        
    }
    
    public function getRelativePath()
    {
        if(is_null($this->relative_path))
        {
            $time = strtotime($this->date_created);
            $this->relative_path = 'saved'.DIRECTORY_SEPARATOR.date('Y', $time).DIRECTORY_SEPARATOR.date('m', $time).DIRECTORY_SEPARATOR.date('d', $time);        
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
        $sql_select = "SELECT d.id, d.name, d.identifier, d.publication_number, d.is_inbound, d.description, d.document_type, d.main_document_type, d.publication_date_from, d.publication_date_to, d.date_received, d.last_updated, u.firstname, u.lastname, u.email ";
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
        $sql_where .= " AND d.main_document_type = :main_document_type";
        
        $params[':active_status'] = Document::ACTIVE_STATUS;
        $params[':main_document_type'] = $this->main_document_type;
        
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
        
        if($this->main_document_type==Document::INTERNAL_USE_TYPE)
            $attribute = 'date_created';
        elseif($this->main_document_type==Document::OUTGOING)
            $attribute = 'publication_date_from';
        else
            $attribute = 'date_received';
        
        
        if(!$this->hasErrors('date_from') && $this->date_from)
        {
            $sql_where .= " AND d.".$attribute." >= :date_from";
            $params[':date_from'] = date('Y-m-d', $this->date_from);
        }
        
        if($this->main_document_type==Document::OUTGOING)
            $attribute = 'publication_date_to';
        
        if(!$this->hasErrors('date_to') && $this->date_to)
        {
            $sql_where .= " AND d.".$attribute." <= :date_to";
            $params[':date_to'] = date('Y-m-d', $this->date_to);            
        }
        
        $sql = $sql_select . $sql_from . $sql_join . $sql_where . $sql_group;
        $qcount = $sql_count . $sql_from . $sql_join . $sql_where;

        $count = Yii::app()->db->createCommand($qcount)->queryScalar($params);
        
        return new CSqlDataProvider(
            $sql,
            array(
                'totalItemCount'=>$count,
                    'sort' => array(
                        'attributes' => array('name', 'identifier'),
                        'defaultOrder' => 'd.date_created DESC'
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
        $sql_select = "SELECT d.id, d.name, d.identifier, d.publication_number, d.is_inbound, d.description, d.document_type, d.main_document_type, d.publication_date_from, d.publication_date_to, d.date_received, d.last_updated, u.firstname, u.lastname, u.email ";
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
            $sql_from = " FROM documents d ";
            $sql_join = " JOIN users u ON u.id=d.creator_id ";
            $sql_join .= " LEFT JOIN documents_rights dr ON dr.document_id = d.id ";
            $sql_join .= " LEFT JOIN users_groups ug ON ug.user_id = d.creator_id ";
            $sql_where = " WHERE (d.creator_id = :user_id OR ug.group_id IN (".implode(",",$user_groups_ids).") )
                                        OR d.creator_id = :user_id 
            ";
            
            $params[':user_id'] = Yii::app()->user->id;
            $params[':admin_right'] = DocumentRight::ADMIN;
        }
        
        $sql_where .= " AND d.status = :inactive_status";
        $sql_where .= " AND d.main_document_type = :main_document_type";
        
        $params[':inactive_status'] = Document::DISABLED_STATUS;
        $params[':main_document_type'] = $this->main_document_type;
        
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
        
        if($this->main_document_type==Document::INTERNAL_USE_TYPE)
            $attribute = 'date_created';
        elseif($this->main_document_type==Document::OUTGOING)
            $attribute = 'publication_date_from';
        else
            $attribute = 'date_received';
        
        
        if(!$this->hasErrors('date_from') && $this->date_from)
        {
            $sql_where .= " AND d.".$attribute." >= :date_from";
            $params[':date_from'] = date('Y-m-d', $this->date_from);
        }
        
        if($this->main_document_type==Document::OUTGOING)
            $attribute = 'publication_date_to';
        
        if(!$this->hasErrors('date_to') && $this->date_to)
        {
            $sql_where .= " AND d.".$attribute." <= :date_to";
            $params[':date_to'] = date('Y-m-d', $this->date_to);            
        }
        
        $sql = $sql_select . $sql_from . $sql_join . $sql_where . $sql_group;
        $qcount = $sql_count . $sql_from . $sql_join . $sql_where;

        $count = Yii::app()->db->createCommand($qcount)->queryScalar($params);
        
        return new CSqlDataProvider(
            $sql,
            array(
                'totalItemCount'=>$count,
                    'sort' => array(
                        'attributes' => array('name', 'identifier'),
                        'defaultOrder' => 'd.date_created DESC'
                ),
                'pagination' => array(
                    'pageSize' => 10
                ),
                'params'=>$params
            )
        );

    }    
    
    public function publicd()
    {
        $this->validate();
        
        $params = array();
        $sql_select = "SELECT d.id, d.name, d.identifier, d.publication_number, d.is_inbound, d.description, d.document_type, d.main_document_type, d.publication_date_from, d.publication_date_to, d.date_received, d.last_updated, u.firstname, u.lastname, u.email ";
        $sql_count = "SELECT COUNT(DISTINCT(d.id)) ";
        
        $sql_group = " GROUP BY d.id";
        $sql_from = " FROM documents d";
        $sql_join = " JOIN users u ON u.id=d.creator_id ";
        $sql_where = " WHERE d.is_visible_to_all = 1";
        $sql_where .= " AND d.status = :active_status";
        $sql_where .= " AND d.main_document_type = :main_document_type";
        
        $params[':active_status'] = Document::ACTIVE_STATUS;
        $params[':main_document_type'] = $this->main_document_type;
        
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
        
        if($this->main_document_type==Document::INTERNAL_USE_TYPE)
            $attribute = 'date_created';
        elseif($this->main_document_type==Document::OUTGOING)
            $attribute = 'publication_date_from';
        else
            $attribute = 'date_received';
        
        
        if(!$this->hasErrors('date_from') && $this->date_from)
        {
            $sql_where .= " AND d.".$attribute." >= :date_from";
            $params[':date_from'] = date('Y-m-d', $this->date_from);
        }
        
        if($this->main_document_type==Document::OUTGOING)
            $attribute = 'publication_date_to';
        
        if(!$this->hasErrors('date_to') && $this->date_to)
        {
            $sql_where .= " AND d.".$attribute." <= :date_to";
            $params[':date_to'] = date('Y-m-d', $this->date_to);            
        }
        
        $sql = $sql_select . $sql_from . $sql_join . $sql_where . $sql_group;
        $qcount = $sql_count . $sql_from . $sql_join . $sql_where;

        $count = Yii::app()->db->createCommand($qcount)->queryScalar($params);
        
        return new CSqlDataProvider(
            $sql,
            array(
                'totalItemCount'=>$count,
                    'sort' => array(
                        'attributes' => array('name', 'identifier'),
                        'defaultOrder' => 'd.date_created DESC'
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
        $sql_select = "SELECT d.id, d.name, d.identifier, d.publication_number, d.is_inbound, d.description, d.document_type, d.main_document_type, d.publication_date_from, d.publication_date_to, d.date_received, d.last_updated, d.last_updater_id ";
        $sql_count = "SELECT COUNT(d.id) ";
        
        $sql_from = " FROM documents d";
        $sql_where = " WHERE creator_id = :user_id AND status = :active_status";
        $sql_where .= " AND d.main_document_type = :main_document_type";
        
        
        $params[':user_id'] = Yii::app()->user->id;
        $params[':active_status'] = Document::ACTIVE_STATUS;
        $params[':main_document_type'] = $this->main_document_type;
        
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
        
        if($this->main_document_type==Document::INTERNAL_USE_TYPE)
            $attribute = 'date_created';
        elseif($this->main_document_type==Document::OUTGOING)
            $attribute = 'publication_date_from';
        else
            $attribute = 'date_received';
        
        
        if(!$this->hasErrors('date_from') && $this->date_from)
        {
            $sql_where .= " AND d.".$attribute." >= :date_from";
            $params[':date_from'] = date('Y-m-d', $this->date_from);
        }
        
        if($this->main_document_type==Document::OUTGOING)
            $attribute = 'publication_date_to';
        
        if(!$this->hasErrors('date_to') && $this->date_to)
        {
            $sql_where .= " AND d.".$attribute." <= :date_to";
            $params[':date_to'] = date('Y-m-d', $this->date_to);            
        }
        
        $sql = $sql_select . $sql_from . $sql_where;
        $qcount = $sql_count . $sql_from . $sql_where;

        $count = Yii::app()->db->createCommand($qcount)->queryScalar($params);
        
        return new CSqlDataProvider(
            $sql,
            array(
                'totalItemCount'=>$count,
                    'sort' => array(
                        'attributes' => array('name', 'identifier'),
                        'defaultOrder' => 'd.date_created DESC'
                ),
                'pagination' => array(
                    'pageSize' => 10
                ),
                'params'=>$params
            )
        );

    }    

    public function dashboard()
    {
        $criteria=new CDbCriteria;

        $criteria->compare('publication_number',$this->publication_number);
        $criteria->compare('name',$this->title,true);
       // $criteria->compare('main_document_type',$this->main_document_type);
        
        $criteria->compare('status', self::ACTIVE_STATUS);
        return new CActiveDataProvider(get_class($this), array(
            'criteria'=>$criteria,
            'sort'=>array(
                'defaultOrder'=>'date_created DESC',
            ),
            'pagination'=>array(
                'pageSize'=>25
            ),
        ));         
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
        $document_id = $this->id;
        $document_title = $this->name;
        $document_identifier = $this->identifier;
        $publication_status = $this->publication_status;

                
        $db = Yii::app()->db->beginTransaction();

        if($this->isSynched())
        {
            $sql = "INSERT INTO documents_deleted (document_id, document_title, document_identifier, deleted_by, deletion_date, is_synched, publication_status) VALUES(:document_id, :document_title, :document_identifier, :deleted_by, CURRENT_TIMESTAMP, :is_synched, :publication_status)";
            if(Yii::app()->db->createCommand($sql)->execute(array(
                ':document_id' => $document_id,
                ':document_title' => $document_title,
                ':document_identifier' => $document_identifier,
                ':deleted_by' => Yii::app()->user->id,
                ':is_synched' => 0,
                ':publication_status' => $publication_status
            )))
            {
                if($this->delete())
                {
                    if(@unlink($path) && $this->removePublishedFile())
                    {
                        @exec('rm -f '.$cache_path.DIRECTORY_SEPARATOR.$name.'_*.jpg');
                        $db->commit();
                        return true;

                    }
                }
            }
        }
        else
        {
            if($this->delete())
            {
                if(@unlink($path))
                {
                    @exec('rm -f '.$cache_path.DIRECTORY_SEPARATOR.$name.'_*.jpg');
                    $db->commit();
                    return true;
                }
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
    
    public function getPeriodDesc()
    {
        if($this->main_document_type==self::INTERNAL_USE_TYPE)
            return "Data di archviazione";
        elseif($this->main_document_type==self::OUTGOING)
            return "Periodo di pubblicazione";
        else
            return "Data di invio/ricezione";
    }
    
    public function isSynched()
    {
        return $this->publication_status == self::PUBLISHED;
    }

    public function removePublishedFile()
    {
        $public_path = $this->getPublicPath();
        if(file_exists($public_path))
        {
            if(@unlink($public_path))
            {
                return true;
            }
            return false;
        }
        return true;
    }

    public function addPublishedFile()
    {
        $public_path = $this->getPublicPath();
        $path = $this->getPath();

        $dirname = dirname($public_path);

        if(file_exists($path))
        {
            if(is_dir($dirname) || (!is_dir($dirname) && mkdir($dirname, 0777, true)))
            {
                if(copy($path, $public_path))
                    return true;
            }
        }
        return false;
    }

    public function syncFile()
    {
        if($this->publication_requested && !file_exists($this->getPublicPath()))
        {
            return $this->addPublishedFile();
        }
        elseif(!$this->publication_requested && file_exists($this->getPublicPath()))
        {
            return $this->removePublishedFile();
        }
        return true;
    }

    protected function getPublicPath()
    {
        return Yii::getPathOfAlias('uploads').DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.$this->getRelativePath().DIRECTORY_SEPARATOR.$this->getDocumentName();
    }
}