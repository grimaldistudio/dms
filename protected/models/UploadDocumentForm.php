<?php

/**
 * Description of UploadDocumentForm
 *
 * @author fabrizio
 */
class UploadDocumentForm extends CFormModel{

    const MAX_SIZE = 16; // 16MB
    
    public $document_file = null;
    public $path = null;
    public $user_id = null;
    
    public function rules()
    {
        return array(
            // username and password are required
            array('document_file', 'required'),
            array('document_file', 'file', 'maxSize'=>self::MAX_SIZE*1024*1024, 'types'=>'pdf')
        );
    }
 
    public function attributeLabels() {
        return array(
            'document_file' => 'Documento da caricare'
        );
    }
    
    public function upload()
    {
        if($this->validate())
        {
            $this->path = DocumentManager::getPendingUserPath($this->user_id, $this->document_file->getName());
            $basepath = dirname($this->path);
            
            if(!is_dir($basepath) && !@mkdir($basepath, 0777, true))
            {
                $this->addError('document_file', $this->document_file->getError());               
                return false;
            }
            
            if($this->document_file->saveAs($this->path))
                return true;
            else
                $this->addError('document_file', $this->document_file->getError());
        }
        return false;
    }
    
}

?>
