<?php

class Spending extends CActiveRecord
{

    const DISABLED_STATUS = 0;
    const ACTIVE_STATUS = 1;
    
    const PUBLISHED = 2;
    const PUBLISHING = 1;
    const NOT_PUBLISHED = 0;
    
    const MAX_OTHER_DOCUMENTS = 3;
    
    const CV_MAX_SIZE = 8;
    const CONTRACT_MAX_SIZE = 8;
    const PROJECT_MAX_SIZE = 8;
    const CAPITULATE_MAX_SIZE = 8; 
    const OTHER_MAX_SIZE = 8;
    
    public $spending_date_from;
    public $spending_date_to;

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
            array('title,receiver,attribution_norm,attribution_mod,office,employee,amount_from,amount_to', 'safe', 'on'=>'search'),
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
            'creator_id' => 'Creato da'
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
        $this->status = self::ENABLED_STATUS;
        return $this->save();
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
        if(file_exists($cv_path))
        {
            if(!@copy($cv_path, $this->getPath().DIRECTORY_SEPARATOR.$this->cv_name))
            {
                $this->addError('cv_name', 'Errore durante la creazione: 12');
                return false;
            }
        }
        
        // if has contract
        $contract_path = $this->getContractPath(true);
        if(file_exists($contract_path))
        {
            if(!@copy($contract_path, $this->getPath().DIRECTORY_SEPARATOR.$this->contract_name))
            {
                $this->addError('contract_name', 'Errore durante la creazione: 13');
                return false;
            }
        }
        
        // if has project
        $project_path = $this->getProjectPath(true);
        if(file_exists($project_path))
        {
            if(!@copy($project_path, $this->getPath().DIRECTORY_SEPARATOR.$this->project_name))
            {
                $this->addError('project_name', 'Errore durante la creazione: 14');
                return false;
            }
        }
        
        // if has capitulate
        $capitulate_path = $this->getCapitulatePath(true);
        if(file_exists($capitulate_path))
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
            self::PUBLISHING => 'Da pubblicare',
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
        
        return $this->processUploadedFile($this->other_file, $dest_path);
    }
    
    public function hasCapitulate()
    {
        if($this->id>0)
        {
            if($this->capitulate_name!=null && $this->capitulate_name!="")
                return true;
        }
        else
        {
            if($this->getCapitulatePath(true))
                return true;            
        }
        return false;
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
            return Yii::getPathOfAlias('uploads').DIRECTORY_SEPARATOR.'spendings'.DIRECTORY_SEPARATOR.$this->id.DIRECTORY_SEPARATOR.$this->getCapitulateName();            
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
        
        $dest_path = $path.DIRECTORY_SEPARATOR.'cap_'.$this->capitulate_file->getName();
        
        return $this->processUploadedFile($this->capitulate_file, $dest_path, $old_path);
    }
    
    public function hasContract()
    {
        if($this->id>0)
        {
            if($this->contract_name!=null && $this->contract_name!="")
                return true;
        }
        else
        {
            if($this->getContractPath(true))
                return true;            
        }
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
        else
            return Yii::getPathOfAlias('uploads').DIRECTORY_SEPARATOR.'spendings'.DIRECTORY_SEPARATOR.$this->id.DIRECTORY_SEPARATOR.$this->getContractName();            
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
        
        $dest_path = $path.DIRECTORY_SEPARATOR.'ctr_'.$this->contract_file->getName();
        
        return $this->processUploadedFile($this->contract_file, $dest_path, $old_path);
    }
    
    public function hasProject()
    {
        if($this->id>0)
        {
            if($this->project_name!=null && $this->project_name!="")
                return true;
        }
        else
        {
            if($this->getProjectPath(true))
                return true;            
        }
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
        else
            return Yii::getPathOfAlias('uploads').DIRECTORY_SEPARATOR.'spendings'.DIRECTORY_SEPARATOR.$this->id.DIRECTORY_SEPARATOR.$this->getProjectName();            
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
        
        $dest_path = $path.DIRECTORY_SEPARATOR.'prj_'.$this->project_file->getName();
        
        return $this->processUploadedFile($this->project_file, $dest_path, $old_path);
    }
    
    public function hasCV()
    {
        if($this->id>0)
        {
            if($this->cv_name!=null && $this->cv_name!="")
                return true;
        }
        else
        {
            if($this->getCVPath(true))
                return true;            
        }
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
            return Yii::getPathOfAlias('uploads').DIRECTORY_SEPARATOR.'spendings'.DIRECTORY_SEPARATOR.$this->id.DIRECTORY_SEPARATOR.$this->getCVName();            
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
        
        $dest_path = $path.DIRECTORY_SEPARATOR.'cv_'.$this->cv_file->getName();
        
        return $this->processUploadedFile($this->cv_file, $dest_path, $old_cv_path);
    }
    
    private function processUploadedFile($uploaded_file, $dest_path, $old_file = null)
    {
        if($uploaded_file->getHasError())
        {
            $this->addError('cv_file', $uploaded_file->getError());
            return false;
        }
        
        if(!file_exists(dirname($dest_path)))
        {
            if(!@mkdir(dirname($dest_path), 0777))
            {
                $this->addError('cv_file', 'Errore durante il caricamento del file: 13');
                return false;
            }
        }
        
        if($old_file!==null)
        {
            if(!@unlink($old_file))
            {
                $this->addError('cv_file', 'Errore durante il caricamento del file: 14');                
                return false;
            }
        }
        
        if(!$uploaded_file->saveAs($dest_path))
        {
            $this->addError('cv_file', 'Errore durante il caricamento del file: 15');            
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
        return $this->status == self::ENABLED;
    }

    public function search()
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
        $criteria->compare('employee',$this->employee,true);
        $criteria->compare('attribution_norm',$this->attribution_norm,true);        
        $criteria->compare('attribution_mod',$this->attribution_mod,true);                
        $criteria->compare('office', $this->office, true);
        
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
        return sprintf("%.2f €", $this->amount);
    }
    
    public function getPublicationRequestedDesc()
    {
        if($this->publication_requested==1)
            return "Si";
        else
            return "No";
    }
}