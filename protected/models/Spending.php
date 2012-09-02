<?php

class Spending extends CActiveRecord
{

    const DISABLED_STATUS = 0;
    const ACTIVE_STATUS = 1;
    
    const PUBLISHED = 1;
    const NOT_PUBLISHED = 0;
    
    const MAX_OTHER_DOCUMENTS = 3;
    
    const CV_MAX_SIZE = 8;
    const CONTRACT_MAX_SIZE = 8;
    const PROJECT_MAX_SIZE = 8;
    const CAPITULATE_MAX_SIZE = 8; 
    const OTHER_MAX_SIZE = 8;
    
    const SEARCH_ALL = 1;
    const SEARCH_MY = 2;
    const SEARCH_DISABLED = 3;
    
    public $spending_date_from;
    public $spending_date_to;
    public $amount_from;
    public $amount_to;
    
    public $cv_file;
    public $contract_file;
    public $capitulate_file;
    public $project_file;
    public $other_file;
    
    public $tmp_files;
    
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
        return 'spendings';
    }

    /**
    * @return array validation rules for model attributes.
    */
    public function rules()
    {
        return array(
            array('title,receiver,attribution_norm,attribution_mod,office,employee,amount,spending_date', 'required', 'on'=>'create,update'),
            array('title,attribution_norm,attribution_mod,employee,office', 'length', 'max'=>255, 'on'=>'create,update'),
            array('description', 'length', 'max'=>2048, 'on'=>'create,update'),
            array('publication_requested', 'safe', 'on'=>'create,update'),
            array('publication_requested', 'default', 'setOnEmpty'=>true, 'value'=>self::NOT_PUBLISHED, 'on'=>'create,update'),                           
            array('spending_date', 'date', 'format'=>'dd/MM/yyyy', 'timestampAttribute'=>'spending_date', 'on'=>'create,update'),            
            array('description','filter','filter'=>array($obj=new CHtmlPurifier(),'purify'), 'on'=>'create,update'),            
            array('receiver', 'length', 'max'=>1024, 'on'=>'create,update'),
            array('title,receiver,amount_from,amount_to', 'safe', 'on'=>'search'),
            array('spending_date_from', 'date', 'format'=>'dd/MM/yyyy', 'timestampAttribute'=>'spending_date_from', 'on'=>'search'),            
            array('spending_date_to', 'date', 'format'=>'dd/MM/yyyy', 'timestampAttribute'=>'spending_date_to', 'on'=>'search'),   
            array('amount_from,amount_to', 'match', 'pattern'=>'/^[0-9]+(\.[0-9]{0,2})?$/', 'on'=>'search'),
            array('amount', 'match', 'pattern'=>'/^[0-9]+(\.[0-9]{0,2})?$/', 'on'=>'create,update'),            
            array('cv_file', 'file', 'types'=>'pdf', 'maxSize'=>self::CV_MAX_SIZE*1024*1024, 'on'=>'cv_upload'),
            array('project_file', 'file', 'types'=>'pdf', 'maxSize'=>self::PROJECT_MAX_SIZE*1024*1024, 'on'=>'project_upload'),
            array('contract_file', 'file', 'types'=>'pdf', 'maxSize'=>self::CONTRACT_MAX_SIZE*1024*1024, 'on'=>'contract_upload'),
            array('capitulate_file', 'file', 'types'=>'pdf', 'maxSize'=>self::CAPITULATE_MAX_SIZE*1024*1024, 'on'=>'capitulate_upload'),
            array('other_file', 'file', 'types'=>'pdf', 'maxSize'=>self::OTHER_MAX_SIZE*1024*1024, 'on'=>'other_upload')            
        );
    }

    /**
    * @return array relational rules.
    */
    public function relations()
    {
        return array(
            'creator' => array(self::BELONGS_TO, 'User', 'creator_id')
        );
    }

    /**
    * @return array customized attribute labels (name=>label)
    */
    public function attributeLabels()
    {
        return array(
            'id' => 'Id',
            'title' => 'Oggetto',
            'receiver' => 'Beneficiario',
            'amount' => 'Importo',
            'attribution_norm' => 'Norma di attribuzione',
            'attribution_mod' => 'Modalità di attribuzione',
            'employee' => 'Responsabile procedimento',
            'office' => 'Uffico responsabile',
            'description' => 'Descrizione',
            'cv_file' => 'CV Incaricato',
            'cv_name' => 'CV Incaricato',
            'contract_file' => 'Contratto',
            'contract_name' => 'Contratto',
            'project_file' => 'Progetto',
            'project_name' => 'Progetto',
            'capitulate_file' => 'Capitolato',
            'capitulate_name' => 'Capitolato',
            'other_file' => 'Altra documentazione',
            'other' => 'Altra documentazione',
            'status' => 'Stato',
            'spending_date' => 'Data', 
            'date_created' => 'Data di creazione',
            'last_updated' => 'Ultimo aggiornamento',
            'spending_date_from' => 'Data da',
            'spending_date_to' => 'Data a',
            'amount_from' => 'Importo min',
            'amount_to' => 'Importo max',
            'publication_status' => 'Pubblicazione albo',
            'publication_requested' => 'Pubblicazione su albo',
            'creator_id' => 'Creato da',
            'search_type' => 'Cerca in:'
        );
    }

    function __toString()
    {
        return $this->title;
    }
    
    public function beforeSave()
    {
        if ($this->isNewRecord){
            if($this->status<=0)
                $this->status = Document::ACTIVE_STATUS;
            $this->date_created = date('Y-m-d H:i:s', time());
            $this->creator_id = Yii::app()->user->id;
        }

        $this->last_updated = new CDbExpression('CURRENT_TIMESTAMP');

        if(is_int($this->spending_date))
            $this->spending_date = date('Y-m-d H:i:s', $this->spending_date);
        return parent::beforeSave();
    }

    public function createSpending()
    {
        $this->is_dirty = 1;
        
        $this->cv_name = $this->getCVName(true);
        $this->project_name = $this->getProjectName(true);
        $this->contract_name = $this->getContractName(true);
        $this->capitulate_name = $this->getCapitulateName(true);
        
        $t = Yii::app()->db->beginTransaction();
        if($this->save())
        {
            if($this->moveFiles())
            {
                $t->commit();
                return true;
            }
        }
        $t->rollback();
        return false;
    }
    
    public function updateSpending()
    {
        $this->is_dirty = 1;
        return $this->save();
    }
    
    public function disable()
    {
        $this->is_dirty = 1;
        $this->status = self::DISABLED_STATUS;
        return $this->save();
    }
    
    public function enable()
    {
        $this->is_dirty = 1;        
        $this->status = self::ACTIVE_STATUS;
        return $this->save();
    }
    
    public function deleteSpending()
    {
        $path = $this->getPath();
        $spending_title = $this->title;
        $spending_id = $this->id;
        $spending_amount = $this->amount;
        $publication_status = $this->publication_status;
        $t = Yii::app()->db->beginTransaction();
        if($this->delete())
        {
            $sql = "INSERT INTO spendings_deleted (spending_id, spending_title, spending_amount, deleted_by, deletion_date, is_synched, publication_status) VALUES(:spending_id, :spending_title, :spending_amount, :deleted_by, CURRENT_TIMESTAMP, :is_synched, :publication_status)";
            if(Yii::app()->db->createCommand($sql)->execute(array(
                ':spending_id' => $spending_id,
                ':spending_title' => $spending_title,
                ':spending_amount' => $spending_amount,
                ':deleted_by' => Yii::app()->user->id,
                ':is_synched' => 0,
                ':publication_status' => $publication_status
            )))
            {
                $t->commit();
                @$this->rrmdir($path);
                return true;
            }
        }
        $t->rollback();
        return false;
    }
    
    public function moveFiles()
    {
        
        if(!@mkdir($this->getPath(), 0777, true))
        {
            $this->addError('id', 'Errore durante la creazione: 10');
            return false;
        }
        
        if(!@mkdir($this->getOtherDir(), 0777, true))
        {
            $this->addError('id', 'Errore durante la creazione: 11');
            return false;
        }
        
        // if has cv
        $cv_path = $this->getCVPath(true);
        if($cv_path!==null)
        {
            if(!@copy($cv_path, $this->getPath().DIRECTORY_SEPARATOR.$this->cv_name))
            {
                $this->addError('cv_name', 'Errore durante la creazione: 12');
                return false;
            }
        }
        
        // if has contract
        $contract_path = $this->getContractPath(true);
        if($contract_path!==null)
        {
            if(!@copy($contract_path, $this->getPath().DIRECTORY_SEPARATOR.$this->contract_name))
            {
                $this->addError('contract_name', 'Errore durante la creazione: 13');
                return false;
            }
        }
        
        // if has project
        $project_path = $this->getProjectPath(true);
        if($project_path!==null)
        {
            if(!@copy($project_path, $this->getPath().DIRECTORY_SEPARATOR.$this->project_name))
            {
                $this->addError('project_name', 'Errore durante la creazione: 14');
                return false;
            }
        }
        
        // if has capitulate
        $capitulate_path = $this->getCapitulatePath(true);
        if($capitulate_path!==null)
        {
            if(!@copy($capitulate_path, $this->getPath().DIRECTORY_SEPARATOR.$this->capitulate_name))
            {
                $this->addError('capitulate_name', 'Errore durante la creazione: 15');
                return false;
            }
        }
        
        // if has others
        foreach($this->listOtherDocuments(true) as $other)
        {
            $other_path = $this->getOtherPath($other, true);
            if(!@copy($other_path, $this->getOtherPath($other)))
            {
                $this->addError('id', 'Errore durante la creazione: 16');
                return false;
            }
        }
        
        @unlink($cv_path);
        @unlink($capitulate_path);
        @unlink($contract_path);
        @unlink($project_path);
        
        foreach($this->listOtherDocuments(true) as $other){
            @unlink($this->getOtherPath($other, true));
        }
        
        return true;
    }
    
    public function getSearchTypeArray()
    {
        return array(
            self::SEARCH_ALL => 'Tutte le spese',
            self::SEARCH_MY => 'Spese create da me',
            self::SEARCH_DISABLED => 'Spese rimosse da elenco'            
        );
    }

    public function getSearchTypeDesc($search_type)
    {
        $search_type_array = $this->getSearchTypeArray();
        if(array_key_exists($search_type, $search_type_array))
            return $search_type_array[$search_type];
        return 'Non definito';
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

    public function listOtherDocuments($tmp = false)
    {
        $other_path = $this->getOtherDir($tmp);
        $files = array();
        if(is_dir($other_path))
            $files =  scandir($other_path);
        else
            $files = array();
        
        $ret = array();
        foreach($files as $file)
        {
            if($file=='.' || $file=='..')
            {
                continue;
            }
            $ret[] = $file;
        }
        return $ret;
    }
    
    public function getOtherDir($tmp = false)
    {
        if($tmp)
            return $this->getTmpPath().DIRECTORY_SEPARATOR.'others';        
        else
            return Yii::getPathOfAlias('uploads').DIRECTORY_SEPARATOR.'spendings'.DIRECTORY_SEPARATOR.$this->id.DIRECTORY_SEPARATOR.'others';        
    }
    
    public function getOtherPath($name, $tmp = false)
    {
        return $this->getOtherDir($tmp).DIRECTORY_SEPARATOR.$name;
    }
    
    public function getOtherSize($name, $tmp = false)
    {
        $path = $this->getOtherPath($name, $tmp);
        $size = filesize($path);
        return sprintf("%.2f", $size/1000.00);
    }
    
    public function downloadOther($name, $force_download = false)
    {
        $path = $this->getOtherPath($name);
        $this->download($path, $force_download);
    }
    
    public function deleteOther($name, $tmp = false)
    {
        $path = $this->getOtherPath($name, $tmp);

        $ret = true;
        if(file_exists($path))
            $ret = @unlink($path);

        return $ret;        
    }
    
    public function processOtherUpload($tmp = false)
    {
        $other_files = $this->listOtherDocuments($tmp);
        if(count($other_files)>=self::MAX_OTHER_DOCUMENTS)
            return false;
        
        $dest_path = $this->getOtherPath($this->other_file->name, $tmp);
        
        return $this->processUploadedFile($this->other_file, $dest_path, null, 'other_file');
    }
    
    public function hasCapitulate($tmp = false)
    {
        if($this->getCapitulatePath($tmp))
            return true;            
    }
    
    public function getCapitulateName($tmp = false)
    {
        if($tmp)
            return str_replace ("cap_", "", basename($this->getCapitulatePath ($tmp)));
        else
            return $this->capitulate_name;
    }
    
    public function getCapitulatePath($tmp = false)
    {
        if($tmp)
        {
            $tmp_files = $this->getTmpFiles();
            if(isset($tmp_files['capitulate']))
            {
                return $this->getTmpPath().DIRECTORY_SEPARATOR.$this->tmp_files['capitulate'];
            }
            else
            {
                return null;
            }
        }
        else
        {
            $path = Yii::getPathOfAlias('uploads').DIRECTORY_SEPARATOR.'spendings'.DIRECTORY_SEPARATOR.$this->id.DIRECTORY_SEPARATOR.$this->getCapitulateName();            
            if($this->getCapitulateName()!==null && $this->getCapitulateName()!=='' && file_exists($path))
                return $path;
            return null;
        }
    }    
    
    public function getCapitulateSize($tmp = false)
    {
        $path = $this->getCapitulatePath($tmp);
        $size = filesize($path);
        return sprintf("%.2f", $size/1000.00);
    }
    
    public function deleteCapitulate($tmp = false)
    {
        $path = $this->getCapitulatePath($tmp);
        $ret = true;
        
        if(file_exists($path))
            $ret = @unlink($path);

        if($ret && !$tmp)
        {
            $this->capitulate_name = new CDbExpression('NULL');
            $this->is_dirty = 1;
            return $this->save();
        }
        else
        {
            return $ret;
        }
    }    

    public function downloadCapitulate($force_download = false)
    {
        $path = $this->getCapitulatePath();
        $this->download($path, $force_download);
    }
    
    public function processCapitulateUpload($tmp = false)
    {
        if($tmp)
        {
            $path = $this->getTmpPath();
        }
        else
        {
            $path = $this->getPath();
        }
        
        $old_path = $this->getCapitulatePath($tmp);
        
        if($tmp)
            $dest_path = $path.DIRECTORY_SEPARATOR.'cap_'.$this->capitulate_file->getName();
        else
            $dest_path = $path.DIRECTORY_SEPARATOR.$this->capitulate_file->getName();            

        if($tmp)
            return $this->processUploadedFile($this->capitulate_file, $dest_path, $old_path, 'capitulate_file');
        else
        {
            $t = Yii::app()->db->beginTransaction();
            $this->project_name = $this->capitulate_file->getName();
            $this->is_dirty = 1;
            if($this->save() && $this->processUploadedFile($this->capitulate_file, $dest_path, $old_path, 'capitulate_file'))
            {
                $t->commit();
                return true;
            }
            $t->rollback();
            return false;
        }
    }
    
    public function hasContract($tmp = false)
    {
        if($this->getContractPath($tmp))
            return true;            
        return false;
    }    
    
    public function getContractName($tmp = false)
    {
        if($tmp)
            return str_replace ("ctr_", "", basename($this->getContractPath ($tmp)));
        else
            return $this->contract_name;
    }

    public function getContractPath($tmp = false)
    {
        if($tmp)
        {
            $tmp_files = $this->getTmpFiles();
            if(isset($tmp_files['contract']))
            {
                return $this->getTmpPath().DIRECTORY_SEPARATOR.$this->tmp_files['contract'];
            }
            else
            {
                return null;
            }
        }
        else{
            $path = Yii::getPathOfAlias('uploads').DIRECTORY_SEPARATOR.'spendings'.DIRECTORY_SEPARATOR.$this->id.DIRECTORY_SEPARATOR.$this->getContractName();            
            if($this->getContractName()!==null && $this->getContractName()!=='' && file_exists($path))
                return $path;
            return null;
        }
    }
    
    public function downloadContract($force_download = false)
    {
        $path = $this->getContractPath();
        $this->download($path, $force_download);
    }
    
    public function getContractSize($tmp = false)
    {
        $path = $this->getContractPath($tmp);
        $size = filesize($path);
        return sprintf("%.2f", $size/1000.00);
    }
    
    public function deleteContract($tmp = false)
    {
        $path = $this->getContractPath($tmp);
        
        $ret = true;
        if(file_exists($path))
            $ret = @unlink($path);
        
        if($ret && !$tmp)
        {
            $this->contract_name = new CDbExpression('NULL');
            $this->is_dirty = 1;
            return $this->save();
        }
        else
        {
            return $ret;
        }
    }
    
    public function processContractUpload($tmp = false)
    {
        if($tmp)
        {
            $path = $this->getTmpPath();
        }
        else
        {
            $path = $this->getPath();
        }
        
        $old_path = $this->getContractPath($tmp);
        
        if($tmp)
            $dest_path = $path.DIRECTORY_SEPARATOR.'ctr_'.$this->contract_file->getName();
        else
            $dest_path = $path.DIRECTORY_SEPARATOR.$this->contract_file->getName();            
    
        if($tmp)
            return $this->processUploadedFile($this->contract_file, $dest_path, $old_path, 'contract_file');
        else
        {
            $t = Yii::app()->db->beginTransaction();
            $this->contract_name = $this->contract_file->getName();
            $this->is_dirty = 1;
            if($this->save() && $this->processUploadedFile($this->contract_file, $dest_path, $old_path, 'contract_file'))
            {
                $t->commit();
                return true;
            }
            $t->rollback();
            return false;
        }
    }
    
    public function hasProject($tmp = false)
    {
        if($this->getProjectPath($tmp))
            return true;            
        return false;
    }
    
    public function getProjectName($tmp = false)
    {
        if($tmp)
            return str_replace ("prj_", "", basename($this->getProjectPath ($tmp)));
        else
            return $this->project_name;
    }
    
    public function getProjectPath($tmp = false)
    {
        if($tmp)
        {
            $tmp_files = $this->getTmpFiles();
            if(isset($tmp_files['project']))
            {
                return $this->getTmpPath().DIRECTORY_SEPARATOR.$this->tmp_files['project'];
            }
            else
            {
                return null;
            }
        }
        else{
            $path = Yii::getPathOfAlias('uploads').DIRECTORY_SEPARATOR.'spendings'.DIRECTORY_SEPARATOR.$this->id.DIRECTORY_SEPARATOR.$this->getProjectName();            
            if($this->getProjectName()!=="" && $this->getProjectName()!==null && file_exists($path))
                return $path;
            return null;
        }
    }
    
    public function downloadProject($force_download = false)
    {
        $path = $this->getProjectPath();
        $this->download($path, $force_download);
    }
    
    public function getProjectSize($tmp = false)
    {
        $path = $this->getProjectPath($tmp);
        $size = filesize($path);
        return sprintf("%.2f", $size/1000.00);
    }
    
    public function deleteProject($tmp = false)
    {
        $path = $this->getProjectPath($tmp);

        $ret = true;
        
        if(file_exists($path))
            $ret = @unlink($path);
        
        if($ret && !$tmp)
        {
            $this->project_name = new CDbExpression('NULL');
            $this->is_dirty = 1;            
            return $this->save();
        }
        else
        {
            return $ret;
        }
    }
    
    public function processProjectUpload($tmp = false)
    {
        if($tmp)
        {
            $path = $this->getTmpPath();
        }
        else
        {
            $path = $this->getPath();
        }
        
        $old_path = $this->getProjectPath($tmp);
        
        if($tmp)
            $dest_path = $path.DIRECTORY_SEPARATOR.'prj_'.$this->project_file->getName();
        else
            $dest_path = $path.DIRECTORY_SEPARATOR.$this->project_file->getName();
             
        if($tmp)
            return $this->processUploadedFile($this->project_file, $dest_path, $old_path, 'project_file');
        else
        {
            $t = Yii::app()->db->beginTransaction();
            $this->project_name = $this->project_file->getName();
            $this->is_dirty = 1;
            if($this->save() && $this->processUploadedFile($this->project_file, $dest_path, $old_path, 'project_file'))
            {
                $t->commit();
                return true;
            }
            $t->rollback();
            return false;
        }
    }
    
    public function hasCV($tmp = false)
    {
        if($this->getCVPath($tmp))
            return true;            
        return false;
    }
    
    public function getCVName($tmp = false)
    {
        if($tmp)
            return str_replace ("cv_", "", basename($this->getCVPath ($tmp)));
        else
            return $this->cv_name;
    }
    
    public function getCVPath($tmp = false)
    {
        if($tmp)
        {
            $tmp_files = $this->getTmpFiles();
            if(isset($tmp_files['cv']))
            {
                return $this->getTmpPath().DIRECTORY_SEPARATOR.$tmp_files['cv'];
            }
            else
            {
                return null;
            }
        }
        else
        {
           $path = Yii::getPathOfAlias('uploads').DIRECTORY_SEPARATOR.'spendings'.DIRECTORY_SEPARATOR.$this->id.DIRECTORY_SEPARATOR.$this->getCVName();                        
           if($this->getCVName()!==null && $this->getCVName()!=='' && file_exists($path))
               return $path;
           return null;
        }

    }
    
    public function downloadCV($force_download = false)
    {
        $path = $this->getCVPath();
        $this->download($path, $force_download);
    }
    
    public function deleteCV($tmp = false)
    {
        $path = $this->getCVPath($tmp);

        $ret = true;
        if(file_exists($path))
            $ret = @unlink($path);
        
        if($ret && !$tmp)
        {
            $this->cv_name = new CDbExpression('NULL');
            $this->is_dirty = 1;            
            return $this->save();
        }        
        else
        {
            return $ret;
        }
    }
    
    public function getCVSize($tmp = false)
    {
        $path = $this->getCVPath($tmp);
        $size = filesize($path);
        return sprintf("%.2f", $size/1000.00);
    }

    public function processCVUpload($tmp = false)
    {
        if($tmp)
        {
            $path = $this->getTmpPath();
        }
        else
        {
            $path = $this->getPath();
        }
        
        $old_cv_path = $this->getCVPath($tmp);
        
        if($tmp)
            $dest_path = $path.DIRECTORY_SEPARATOR.'cv_'.$this->cv_file->getName();
        else
            $dest_path = $path.DIRECTORY_SEPARATOR.$this->cv_file->getName();
        
        if($tmp)
            return $this->processUploadedFile($this->cv_file, $dest_path, $old_cv_path, 'cv_file');
        else
        {
            $t = Yii::app()->db->beginTransaction();
            $this->cv_name = $this->cv_file->getName();
            $this->is_dirty = 1;
            if($this->save() && $this->processUploadedFile($this->cv_file, $dest_path, $old_cv_path, 'cv_file'))
            {
                $t->commit();
                return true;
            }
            $t->rollback();
            return false;
        }
    }
    
    private function processUploadedFile($uploaded_file, $dest_path, $old_file = null, $file_attribute = 'cv_file')
    {
        if($uploaded_file->getHasError())
        {
            $this->addError($file_attribute, $uploaded_file->getError());
            return false;
        }
        
        if(file_exists($dest_path) && $dest_path!==$old_file)
        {
            // duplicate file
            $this->addError($file_attribute, 'E\' già stato caricato un file con lo stesso nome: 09');
            return false;
        }
        
        if(!file_exists(dirname($dest_path)))
        {
            if(!@mkdir(dirname($dest_path), 0777))
            {
                $this->addError($file_attribute, 'Errore durante il caricamento del file: 13');
                return false;
            }
        }
        
        if($old_file!==null)
        {
            if(!@unlink($old_file))
            {
                $this->addError($file_attribute, 'Errore durante il caricamento del file: 14');                
                return false;
            }
        }
        
        if(!$uploaded_file->saveAs($dest_path))
        {
            $this->addError($file_attribute, 'Errore durante il caricamento del file: 15');            
            return false;
        }
        return true;
    }
    
    private function download($path, $force_download)
    {
        if(file_exists($path)){
            if($force_download)
                header('Content-disposition: attachment; filename='.$this->getCVName());
            header('Content-type: application/pdf');
            readfile($path);
            Yii::app()->end();
        }
        else {
            throw new CHttpException(404, 'File non trovato');
        }
    }
    

    public function isActive()
    {
        return $this->status == self::ACTIVE_STATUS;
    }

    public function search($search_type = self::SEARCH_ALL)
    {
        $this->validate();
        $criteria=new CDbCriteria;

        if(!$this->hasErrors('spending_date_from') && $this->spending_date_from)
            $criteria->addCondition("spending_date>='".date('Y-m-d H:i:s', $this->spending_date_from)."'");
        
        if(!$this->hasErrors('spending_date_to') && $this->spending_date_to)
            $criteria->addCondition("spending_date<='". date('Y-m-d H:i:s', $this->spending_date_to)."'");
        
        if(!$this->hasErrors('amount_from') && $this->amount_from)
        {
            $criteria->addCondition("amount>='". $this->amount_from."'");            
        }
        
        if(!$this->hasErrors('amount_to') && $this->amount_to)
        {
            $criteria->addCondition("amount<='". $this->amount_to."'");            
        }
        
        $criteria->compare('title',$this->title,true);
        $criteria->compare('receiver',$this->receiver,true);

        if($search_type == self::SEARCH_DISABLED)
        {
            if(!Yii::app()->user->isAdmin())
                $criteria->compare('creator_id', Yii::app()->user->id);
            $criteria->compare('status', self::DISABLED_STATUS);
        }
        elseif($search_type == self::SEARCH_MY)
        {
            if(!Yii::app()->user->isAdmin())
                $criteria->compare('creator_id', Yii::app()->user->id);
        }

        return new CActiveDataProvider(get_class($this), array(
            'criteria'=>$criteria,
            'sort'=>array(
                'defaultOrder'=>'t.spending_date DESC',
            ),
            'pagination'=>array(
                'pageSize'=>5
            ),
        ));			
    }
    
    public function getTitle()
    {
        return $this->title;
    }
    

    private function loadTmpFiles()
    {
        $tmp_path = $this->getTmpPath();
        if(is_dir($tmp_path))
            $files = scandir($tmp_path);
        else
            $files = array();
        $result = array();
        foreach($files as $file)
        {
            if($file=='.' || $file=='..')
                continue;
            
            if(strstr($file, "cv_")!==FALSE)
            {
                $result['cv'] = $file;
                continue;
            }

            if(strstr($file, "ctr_")!==FALSE)
            {
                $result['contract'] = $file;
                continue;
            }            

            if(strstr($file, "prj_")!==FALSE)
            {
                $result['project'] = $file;
                continue;
            }            
            
            if(strstr($file, "cap_")!==FALSE)
            {
                $result['capitulate'] = $file;
                continue;
            }            
            
            if($file=="others")
            {
                $result['others'] = array();
                $other_files = scandir($tmp_path.DIRECTORY_SEPARATOR.'others');
                foreach($other_files as $other_file)
                {
                    if($other_file!='.' && $other_file!='..')
                        $result['others'][] = $other_file;
                }
            }
            
        }
        
        return $result;
    }
    
    public function canAddNewOther($tmp = false)
    {
        $other_files = $this->listOtherDocuments($tmp);
        if(count($other_files)<self::MAX_OTHER_DOCUMENTS)
            return true;
        return false;
    }
    
    public function getTmpFiles()
    {
        if(is_null($this->tmp_files))
        {
            $this->tmp_files = $this->loadTmpFiles();
        }
        return $this->tmp_files;
    }

    public function getTmpPath()
    {
        return Yii::getPathOfAlias('tmp_files').DIRECTORY_SEPARATOR.'spendings'.DIRECTORY_SEPARATOR.Yii::app()->user->id;
    }
    
    public function getPath()
    {
        return Yii::getPathOfAlias('uploads').DIRECTORY_SEPARATOR.'spendings'.DIRECTORY_SEPARATOR.$this->id;
    }
    
    public function getDisplayAmount()
    {
        return sprintf("€ %.2f", $this->amount);
    }
    
    public function getPublicationRequestedDesc()
    {
        if($this->publication_requested==1)
            return "Si";
        else
            return "No";
    }

    protected function rrmdir($dir) {
        foreach(glob($dir . '/*') as $file) {
            if(is_dir($file))
                return rrmdir($file);
            else
                return unlink($file);
        }
        return rmdir($dir);
    }
}