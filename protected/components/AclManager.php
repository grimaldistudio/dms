<?php

/**
 * This class is responsible for permission
 * check of the logged in user
 * 
 * @author Fabrizio D'Ammassa
 */
class AclManager {

    const PERMISSION_DENIED = 0;
    const PERMISSION_READ = 1;
    const PERMISSION_WRITE = 2;
    const PERMISSION_ADMIN = 4;
    
    static function isAllowed($right_id)
    {
        if(Yii::app()->user->getIsGuest())
            return false;
        
        if(Yii::app()->user->isAdmin())
            return true;
        
        $user_roles = Yii::app()->user->getRolesData();
        $right = Right::model()->findByRoleIds($right_id, array_keys($user_roles));
        if($right)
            return true;
        return false;
    }
    
    static function isDocumentVisible($document_id)
    {
        $query = "SELECT is_visible_to_all FROM documents d WHERE d.id=:document_id";
        $is_visible_to_all = Yii::app()->db->createCommand($query)->queryScalar(array(':document_id'=>$document_id));

        if($is_visible_to_all==1 || $is_visible_to_all=='1')
            return true;
        return false;
    }

    static function hasReadPrivilege($user_id, $group_ids = array(), $document_id, $is_admin = false)
    {
        if($is_admin)
            return true;

        if(self::isDocumentVisible($document_id))
            return true;

        $query = "SELECT count(1) FROM documents d WHERE d.id=:document_id AND d.creator_id=:user_id";
        $count = Yii::app()->db->createCommand($query)->queryScalar(array(':user_id'=>$user_id, ':document_id'=>$document_id));
        if($count>0)
            return true;
        
        if(count($group_ids)==0)
        {
            $group_ids = array(0);
        }
        
        $query = "SELECT dr.* FROM documents_rights dr
                            JOIN documents d ON d.id=dr.document_id
                            WHERE d.id=:document_id 
                            AND (dr.user_id=:user_id
                            OR dr.group_id IN (".implode(",",$group_ids)."))
                            ORDER BY dr.user_id DESC, dr.group_id DESC
                            LIMIT 1
                ";

        $dr = Yii::app()->db->createCommand($query)->queryRow(true, array(':user_id'=>$user_id, ':document_id'=>$document_id));
        if($dr)
        {
            if($dr['right'] & self::PERMISSION_READ)
                return true;
        }
        return false;
    }
    
    static function hasWritePrivilege($user_id, $group_ids = array(), $document_id, $is_admin = false)
    {
        if($is_admin)
            return true;       
         
        $query = "SELECT count(1) FROM documents d WHERE d.id=:document_id AND d.creator_id=:user_id";
        $count = Yii::app()->db->createCommand($query)->queryScalar(array(':user_id'=>$user_id, ':document_id'=>$document_id));
        if($count>0)
            return true;

        if(count($group_ids)==0)
        {
            $group_ids = array(0);
        }
        
        $query = "SELECT dr.* FROM documents_rights dr
                            JOIN documents d ON d.id=dr.document_id
                            WHERE d.id=:document_id 
                            AND (dr.user_id=:user_id
                                OR dr.group_id IN (".implode(",",$group_ids).")
                            )
                            ORDER BY dr.user_id DESC, dr.group_id DESC
                            LIMIT 1
                ";

        $dr = Yii::app()->db->createCommand($query)->queryRow(true, array(':user_id'=>$user_id, ':document_id'=>$document_id));
        if($dr)
        {
            if($dr['right'] & self::PERMISSION_WRITE)
                return true;
        }
        return false;        
        
    }
    
    static function hasAdminPrivilege($user_id, $group_ids = array(), $document_id, $is_admin = false)
    {
        if($is_admin)   
            return true;

        $query = "SELECT count(1) FROM documents d WHERE d.id=:document_id AND d.creator_id=:user_id";
        $count = Yii::app()->db->createCommand($query)->queryScalar(array(':user_id'=>$user_id, ':document_id'=>$document_id));
        if($count>0)
            return true;         

        if(count($group_ids)==0)
        {
            $group_ids = array(0);
        }
        
        $query = "SELECT dr.* FROM documents_rights dr
                            JOIN documents d ON d.id=dr.document_id
                            WHERE d.id=:document_id 
                            AND (dr.user_id=:user_id
                                OR dr.group_id IN (".implode(",",$group_ids).")
                            )
                            ORDER BY dr.user_id DESC, dr.group_id DESC
                            LIMIT 1
                ";

        $dr = Yii::app()->db->createCommand($query)->queryRow(true, array(':user_id'=>$user_id, ':document_id'=>$document_id));
        if($dr)
        {
            if($dr['right'] & self::PERMISSION_ADMIN)
                return true;
        }
        return false;        
    }
    
    static function getDocumentPrivilege($user_id, $group_ids = array(), $document_id, $is_admin = false)
    {
        if($is_admin)   
            return self::PERMISSION_ADMIN|self::PERMISSION_READ|self::PERMISSION_WRITE;

        $query = "SELECT count(1) FROM documents d WHERE d.id=:document_id AND d.creator_id=:user_id";
        $count = Yii::app()->db->createCommand($query)->queryScalar(array(':user_id'=>$user_id, ':document_id'=>$document_id));
        if($count>0)
            return self::PERMISSION_ADMIN|self::PERMISSION_READ|self::PERMISSION_WRITE;
        
        $query = "SELECT dr.* FROM documents_rights dr
                                JOIN documents d ON d.id=dr.document_id
                                WHERE d.id=:document_id 
                                AND (dr.user_id=:user_id
                                    OR dr.group_id IN (".implode(",",$group_ids).")
                                )
                                ORDER BY dr.user_id DESC, dr.group_id DESC
                                LIMIT 1
        ";

        $dr = Yii::app()->db->createCommand($query)->queryRow(true, array(':user_id'=>$user_id, ':document_id'=>$document_id));
        if($dr)
        {
            return $dr['right'];
        }
        return self::PERMISSION_DENIED;
    }
    
    public static function getDocumentTargets($document_id)
    {
        $sql = "SELECT u.firstname, u.lastname, u.email, g.name, g.email FROM documents_rights dr 
                                                                        LEFT JOIN users u ON u.id=dr.user_id
                                                                        LEFT JOIN groups g ON g.id=dr.group_id
                                                                        WHERE dr.document_id = :document_id";
        
        $rows = Yii::app()->db->createCommand($sql)->queryAll(true, array(':document_id'=>$document_id));
        return $rows;
    }
    
}

?>
