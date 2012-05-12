<?php

/**
 * It performs some operations with files
 *
 * @author fabrizio
 */
class FileManager {

    
    public function scanFolder($folder)
    {
        $result = array();

        if(!is_dir($folder))
            return $result;

        $handle =  opendir($folder);
        while ($datei = readdir($handle))
        {
            if (($datei != '.') && ($datei != '..'))
            {
                $file = $datei;
                if (!is_dir($file)) {
                    $result[] = $file;
                }
            }
        }
        closedir($handle);
        return $result;        
    }

    static function compare_files($a, $b){
            $a_time = $a['ctime'];
            $b_time = $b['ctime'];
            if($a_time == $b_time)
                    return 0;
            return $a_time < $b_time? 1: -1;
    }
	
    public function getPendingDocuments($groups, $user_id, $time = 0)
    {
        $basepath = Yii::getPathOfAlias('uploads').DIRECTORY_SEPARATOR.'groups';
        $documents = array();
        foreach($groups as $group_id => $group_data)
        {
            $g_documents = array();
            $folder = $basepath.DIRECTORY_SEPARATOR.$group_data['folder_name'].DIRECTORY_SEPARATOR.'pending';
            $files = $this->scanFolder($folder);
            foreach($files as $file)
            {
                $filepath = $folder.'/'.$file;
                $type = substr($file, strrpos($file, '.') + 1);
                if($type=='pdf'){
                    $doc = stat($filepath);
                    $doc['path'] = $filepath;
                    $doc['name'] = $file;
                    if($doc['mtime']>$time || $doc['ctime']>$time){
                        $doc['new'] = 1;
                    }
                    $g_documents[] = $doc;
                }                
            }
            if(count($g_documents)>0)
            {
                usort ($g_documents, "FileManager::compare_files");
                $documents[$group_id] = $g_documents; 
            }
        }
        // scan user directory
        $ubasepath = Yii::getPathOfAlias('uploads').DIRECTORY_SEPARATOR.'users';
        $u_documents = array();
        $folder = $ubasepath.'/u_'.$user_id.'/pending';
        $files = $this->scanFolder($folder);
        foreach($files as $file)
        {
            $filepath = $folder.'/'.$file;
            $type = substr($file, strrpos($file, '.') + 1);
            if($type=='pdf'){
                $doc = stat($filepath);
                $doc['path'] = $filepath;
                $doc['name'] = $file;
                if($doc['mtime']>$time || $doc['ctime']>$time){
                    $doc['new'] = 1;
                }
                $u_documents[] = $doc;
            }                
        }
        if(count($u_documents)>0)
        {
            usort ($u_documents, "FileManager::compare_files");
            $documents['user'] = $u_documents; 
        }        

        return $documents;
    }
    

    public function hasUpdates($groups, $user_id, $time = 0)
    {
        $basepath = Yii::getPathOfAlias('uploads').DIRECTORY_SEPARATOR.'groups';
        foreach($groups as $group_data)
        {
            $folder = $basepath.DIRECTORY_SEPARATOR.$group_data['folder_name'].DIRECTORY_SEPARATOR.'pending';
            $files = $this->scanFolder($folder);
            foreach($files as $file)
            {
                $filepath = $folder.'/'.$file;
                $type = substr($file, strrpos($file, '.') + 1);
                if($type=='pdf'){
                    $doc = stat($filepath);
                    $doc['path'] = $filepath;
                    $doc['name'] = $file;
                    if($doc['mtime']>$time || $doc['ctime']>$time){
                        return true;
                    }
                }                
            }
        }
        $ubasepath = Yii::getPathOfAlias('uploads').DIRECTORY_SEPARATOR.'users';        
        $folder = $ubasepath.'/u_'.$user_id.'/pending';
        $files = $this->scanFolder($folder);
        foreach($files as $file)
        {
            $filepath = $folder.'/'.$file;
            $type = substr($file, strrpos($file, '.') + 1);
            if($type=='pdf'){
                $doc = stat($filepath);
                $doc['path'] = $filepath;
                $doc['name'] = $file;
                if($doc['mtime']>$time || $doc['ctime']>$time){
                    return true;
                }
            }                
        }        
        return false;
    }
}

?>
