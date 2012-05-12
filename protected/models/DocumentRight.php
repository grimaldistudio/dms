<?php

class DocumentRight extends CActiveRecord
{

    const READ = 1;
    const WRITE = 3;
    const ADMIN = 7;
    
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
        return 'documents_rights';
    }

    /**
    * @return array validation rules for model attributes.
    */
    public function rules()
    {
        return array(
        );
    }

    /**
    * @return array relational rules.
    */
    public function relations()
    {
        return array(
            'user' => array(self::BELONGS_TO, 'User', 'user_id' ),
            'document' => array(self::BELONGS_TO, 'Document', 'document_id'),
            'group' => array(self::BELONGS_TO, 'Group', 'group_id')
        );
    }

    /**
    * @return array customized attribute labels (name=>label)
    */
    public function attributeLabels()
    {
        return array(
            'id' => 'Id',
            'document_id' => 'Documento',
            'user_id' => 'Utente',
            'group_id' => 'Gruppo',
            'date_created' => 'Data di creazione',
            'last_updated' => 'Ultimo aggiornamento'
        );
    }

    public function beforeSave()
    {
        if ($this->isNewRecord){
            $this->date_created = new CDbExpression('CURRENT_TIMESTAMP');
        }
        $this->last_updated = new CDbExpression('CURRENT_TIMESTAMP');
        return parent::beforeSave();
    }

    public function getPrivilegeArray()
    {
        return array(
            self::READ => 'Lettura',
            self::WRITE => 'Modifica',
            self::ADMIN => 'Amministrazione'
        );
    }
    
    public function getPrivilegeDesc()
    {
        $arr = $this->getPrivilegeArray();
        if(isset($arr[$this->right]))
            return $arr[$this->right];
        return 'n/d';
    }
    
    public function findAllByDocumentId($document_id)
    {
        $sql = "SELECT dr.id, dr.right, dr.user_id, dr.group_id, g.name, g.email as g_email, u.firstname, u.lastname, u.email as u_email 
                    FROM documents_rights dr 
                    LEFT JOIN users u ON dr.user_id=u.id
                    LEFT JOIN groups g ON dr.group_id=g.id
                    WHERE dr.document_id=:document_id";
        
        $cmd = Yii::app()->db->createCommand($sql);
        $rows = $cmd->queryAll(true, array(':document_id'=>$document_id));
        return $rows;
    }
    
    public function updateRights($document_id, $rights_data)
    {
        if(isset($rights_data['group_ids']))
        {
            if(isset($rights_data['user_ids']))
            {
                
                $user_ids = explode(',', $rights_data['user_ids']);
                $safe_user_ids = array();
                foreach($user_ids as $user_id)
                {
                    if($user_id!=Yii::app()->user->id && intval($user_id)>0)
                        $safe_user_ids[] = intval($user_id);
                }
                
                $t = Yii::app()->db->beginTransaction();
                // users
                // 
                // delete perms where u_id is not null and not in new user_ids
                if(count($safe_user_ids)>0)
                {
                    $sql = "DELETE FROM documents_rights WHERE document_id = :document_id AND user_id IS NOT NULL and user_id NOT IN (".implode(',', $safe_user_ids).")";
                    $cmd = Yii::app()->db->createCommand($sql);
                    $cmd->execute(array(':document_id'=>$document_id));
                }

                // update perms where u_id is in new user_ids
                $sql = "UPDATE documents_rights dr SET dr.right = :right, last_updated = CURRENT_TIMESTAMP WHERE dr.user_id = :user_id AND dr.document_id = :document_id";
                $cmd = Yii::app()->db->createCommand($sql);
                $insert_sql = "INSERT INTO documents_rights (document_id, user_id, `right`, date_created, last_updated) VALUES(:document_id, :user_id, :right, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
                $insert_cmd = Yii::app()->db->createCommand($insert_sql);
                foreach($safe_user_ids as $safe_user_id)
                {
                    if(isset($rights_data['u_'.$safe_user_id]))
                    {
                        $new_right = intval($rights_data['u_'.$safe_user_id]);
                        if(in_array($new_right, array_keys($this->getPrivilegeArray())))
                        {
//                            $count  = $select_cmd->execute(array(':document_id'=>$document_id, ':user_id'=>$user_id))
                            $affected = $cmd->execute(array(':document_id'=>$document_id, ':user_id'=>$safe_user_id, ':right'=>$new_right));
/*                            var_dump($document_id);
                            var_dump($user_id);
                            var_dump($new_right);
                            var_dump($affected);
                            exit();*/
                            if($affected==0)
                            {
                                // create new record
                                $insert_cmd->execute(array(':document_id'=>$document_id, ':user_id'=>$safe_user_id, ':right'=>$new_right));
                                // new_user_added
                                $this->newDocumentAdded($document_id, $safe_user_id, $new_right);
                            }
                        }
                    }
                }
                
                $group_ids = explode(',', $rights_data['group_ids']);
                $safe_group_ids = array();
                foreach($group_ids as $group_id)
                {
                    if(intval($group_id)>0)
                        $safe_group_ids[] = intval($group_id);
                }
                
                // groups
                // 
                if(count($safe_group_ids)>0)
                {
                    // delete perms where u_id is not null and not in new user_ids
                    $sql = "DELETE FROM documents_rights WHERE document_id = :document_id AND group_id IS NOT NULL and group_id NOT IN (".implode(',', $safe_group_ids).")";
                    $cmd = Yii::app()->db->createCommand($sql);
                    $cmd->execute(array(':document_id'=>$document_id));
                }
                
                // update perms where u_id is in new user_ids
                $sql = "UPDATE documents_rights dr SET dr.right = :right, last_updated = CURRENT_TIMESTAMP WHERE dr.group_id = :group_id AND dr.document_id = :document_id";
                $cmd = Yii::app()->db->createCommand($sql);
                $insert_sql = "INSERT INTO documents_rights (document_id, group_id, `right`, date_created, last_updated) VALUES(:document_id, :group_id, :right, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
                $insert_cmd = Yii::app()->db->createCommand($insert_sql);
                foreach($safe_group_ids as $safe_group_id)
                {
                    if(isset($rights_data['g_'.$safe_group_id]))
                    {
                        $new_right = intval($rights_data['g_'.$safe_group_id]);
                        if(in_array($new_right, array_keys($this->getPrivilegeArray())))
                        {
                            $affected = $cmd->execute(array(':document_id'=>$document_id, ':group_id'=>$safe_group_id, ':right'=>$new_right));
                            if($affected==0)
                            {
                                // create new record
                                $insert_cmd->execute(array(':document_id'=>$document_id, ':group_id'=>$safe_group_id, ':right'=>$new_right));
                            }
                        }
                    }
                }
                
                $t->commit();
                return true;
            }
        }
        return false;
    }
    
    public function addPermission($user_id, $document_id, $permission)
    {
        if($permission>0)
        {
            $update_sql = "UPDATE documents_rights SET `right` = :right, last_updated = CURRENT_TIMESTAMP WHERE user_id = :user_id AND document_id = :document_id";
            $insert_sql = "INSERT INTO documents_rights(user_id, document_id, `right`, date_created, last_updated) VALUES(:user_id, :document_id, :right, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
            $affected_rows = Yii::app()->db->createCommand($update_sql)->execute(array(':right'=>$permission, ':user_id'=>$user_id, ':document_id'=>$document_id));
            if($affected_rows==0)
            {
                Yii::app()->db->createCommand($insert_sql)->execute(array(':right'=>$permission, ':user_id'=>$user_id, ':document_id'=>$document_id));            
                $this->newDocumentAdded($document_id, $user_id, $permission);            
            }
        }
        return true;
    }
    
    public function newDocumentAdded($document_id, $user_id, $new_right)
    {
        // create notification
        $sql = "INSERT INTO user_notifications(user_id, document_id, description, link, date_created, last_updated) VALUES(:user_id, :document_id, :description, :link, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
        $params = array();
        $params[':document_id'] = $document_id;
        $params[':user_id'] = $user_id;
        $params[':description'] = "Nuovo documento assegnato";
        $params[':link'] = Yii::app()->createAbsoluteUrl('/document/view', array('id'=>$document_id));
        if(Yii::app()->db->createCommand($sql)->execute($params))
            return true;
        return false;
    }
   
}