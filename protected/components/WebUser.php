<?php
class WebUser extends CWebUser{
	
    // Store model to not repeat query.
    private $_model;
    private $_groups; // this is an array group_id=>array('name'=>group_name,'folder_name'=>group_folder_name)
    private $_roles; // this is an array role_id=>role_name
    private $_rights; // this is an array right_key=>right_name
    
    private $_documents_privileges = array();
    
    function getModel()
    {
        return $this->loadUser();
    }

    function isLoggedIn()
    {
        return !$this->getIsGuest();
    }
    
    function getGroupFolderName($group_id)
    {
        if($this->isAdmin())
        {
            $group = Group::model()->findByPk($group_id);
            if($group)
                return $group->folder_name;
        }   
        else
        {
            $this->_groups = $this->getGroups();
            if(isset($this->_groups[$group_id]))
            {
                return $this->_groups[$group_id]['folder_name'];
            }
        }
        return FALSE;
    }
    
    function refreshGroupsData()
    {
        // get groups
        $groups_data = array();
        $groups = Group::model()->findAllByUserId($this->id);
        foreach($groups as $group)
        {
            $group_data_item = array();
            $group_data_item['group_id'] = $group->id;
            $group_data_item['group_name'] = $group->name;
            $group_data_item['group_folder_name'] = $group->folder_name;
            $groups_data[] = $group_data_item;
        }
        $this->setState('groups', json_encode($groups_data));
        $this->_groups = null;
        $this->refreshGroupsData();
    }

    function refreshRolesData()
    {
        // get roles
        $roles_data = array();
        $roles = Role::model()->findAllByUserId($this->id);
        foreach($roles as $role)
        {
            $role_data_item = array();
            $role_data_item['role_id'] = $role->id;
            $role_data_item['role_name'] = $role->name;
            $roles_data[] = $role_data_item;
        }
        $this->setState('roles', json_encode($roles_data));
        $this->_roles = null;
        $this->refreshRolesData();
    }

    function refreshPersonalData()
    {
        $this->_model = null;
        $user = $this->loadUser();
        $this->setState('firstname', $user->getState('firstname'));
        $this->setState('lastname', $user->getState('lastname'));
        $this->setState('telephone', $user->getState('telephone'));
        $this->setState('is_admin', $user->getState('is_admin'));
        $this->setState('last_login', $user->getState('last_login'));        
    }

    function refreshAll()
    {
        $this->refreshPersonalData();
        if(!$this->isAdmin())
        {
            $this->refreshGroupsData();
            $this->refreshRolesData();
        }
    }
    
    function getGroups()
    {
        return $this->loadGroupsData();
    }

    function getRoles()
    {
        return $this->loadRolesData();
    }
    
    function getRights()
    {
        return $this->loadRightsData();
    }
    
    function belongsToGroup($group_id)
    {
        if(Yii::app()->user->isAdmin())
            return true;
        return in_array($group_id, array_keys($this->getGroups()));
    }
    
    function hasRole($role_id=0)
    {
        if(is_int($role_id))
        {
            return in_array($role_id, array_keys($this->getRoles()));
        }
        else
        {
            return in_array($role_id, $this->getRoles());
        }
    }
    
    // This is a function that checks the field 'role'
    // in the User model to be equal to 1, that means it's admin
    // access it by Yii::app()->user->isAdmin()
    function isAdmin(){
        return intval($this->is_admin)==1;
    }

    function isSuperadmin()
    {
        if(!$this->isAdmin())
            return false;

        if(in_array($this->name, Yii::app()->params['superadmins']))
            return true;

        return false;	
    }

    // Load user model.
    protected function loadUser($id=null)
    {
        if($this->_model===null)
        {
            if($id!==null)
                $this->_model=User::model()->findByPk($id);
            else
                $this->_model=User::model()->findByPk($this->id);
        }
        return $this->_model;
    }
	
    protected function loadGroupsData()
    {
        if($this->_groups === null)
        {
            $unserialized__groups = json_decode($this->getState('groups'));
            $this->_groups = array();
            if(is_array($unserialized__groups))
            {
                foreach($unserialized__groups as $unserialized__group)
                {
                    $this->_groups[$unserialized__group->group_id] = array('name'=>$unserialized__group->group_name, 'folder_name'=>$unserialized__group->group_folder_name);
                }
            }
        }
        return $this->_groups;
    }
    
    protected function loadRolesData()
    {
        if($this->_roles === null)
        {
            $unserialized_roles = json_decode($this->getState('roles'));
            $this->_roles = array();            
            if(is_array($unserialized_roles))
            {
                foreach($unserialized_roles as $unserialized_role)
                {
                    $this->_roles[$unserialized_role->role_id] = $unserialized_role->role_name;
                }
            }
        }
        return $this->_roles;        
    }

    protected function loadRightsData()
    {
        if($this->_rights === null)
        {
            $roles = $this->getRoles();
            $rights = Right::model()->findAllByRoleIds(array_keys($roles));
            $this->_rights = array();
            foreach($rights as $right)
            {
                $this->_rights[$right->key] = $right->name;
            }
        }
        return $this->_rights;
    }
    
    public function hasDocumentPrivilege($document_id, $privilege = AclManager::PERMISSION_READ)
    {
        if(!isset($this->_documents_privileges[$document_id]))
        {
            $this->_documents_privileges[$document_id] = AclManager::getDocumentPrivilege($this->id, array_keys($this->getGroups()), $document_id, $this->isAdmin());
        }

        if(isset($this->_documents_privileges[$document_id]))
            return $this->_documents_privileges[$document_id] & $privilege;
        
        return false;
    }
    
}