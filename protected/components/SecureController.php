<?php
/**
 * Controller is the customized base controller class.
 * All controller classes for this application should extend from this base class.
 */
class SecureController extends CController
{
    
        public function filters()
        {
            return array('accessControl');
        }
        
        public function accessRules()
        {
            return array(
                array('allow', 'expression'=>'Yii::app()->authgateway->isAllowed()'),
                array('deny')
            );
        }
        
	/**
	 * @var string the default layout for the controller view. Defaults to '//layouts/column1',
	 * meaning using a single column layout. See 'protected/views/layouts/column1.php'.
	 */
	public $layout='//layouts/dms_sidebar';
	/**
	 * @var array context menu items. This property will be assigned to {@link CMenu::items}.
	 */
	public $menu=array();
        
        /**
	 * @var array context menu items. This property will be user to build the header menu
	 */
        public $h_menu=array();
        
	/**
	 * @var array the breadcrumbs of the current page. The value of this property will
	 * be assigned to {@link CBreadcrumbs::links}. Please refer to {@link CBreadcrumbs::links}
	 * for more details on how to specify this property.
	 */
	public $breadcrumbs=array();
        
        public function __construct($id, $module = null) {
            Yii::app()->bootstrap->registerAlert('.alert');
            Yii::app()->clientScript->registerScriptFile('/js/dms.js');
            parent::__construct($id, $module);
        }

        protected function buildMenu()
        {
            $items = array();
            
            // home
            $home = array(
                        'label'=>'Home', 
                        'icon'=>'home', 
                        'url'=>Yii::app()->homeUrl, 
                        'active'=>$this->id=='site' && $this->action->id=='index'
                    );
            
            // documents
            $archive_search = array(
                        'label'=>'Archivio', 
                        'url'=>array('/document/search', 'doc_type'=>Document::INTERNAL_USE_TYPE),                
                        'visible'=>Yii::app()->authgateway->isAllowed('document', 'search'),
                        'active'=>$this->id=='document' && $this->action->id=='search' && (isset($_GET['doc_type']) && $_GET['doc_type']==Document::INTERNAL_USE_TYPE)
                    );
            
            $protocol_search = array(
                        'label'=>'Posta in entrata', 
                        'url'=>array('/document/search', 'doc_type'=>Document::INBOX),                
                        'visible'=>Yii::app()->authgateway->isAllowed('document', 'search'),
                        'active'=>$this->id=='document' && $this->action->id=='search' && (!isset($_GET['doc_type']) || $_GET['doc_type']==Document::INBOX)
                    );
            
            $publish_search = array(
                        'label'=>'Documenti pubblici', 
                        'url'=>array('/document/search', 'doc_type'=>Document::OUTGOING),                
                        'visible'=>Yii::app()->authgateway->isAllowed('document', 'search'),
                        'active'=>$this->id=='document' && $this->action->id=='search' && (isset($_GET['doc_type']) && $_GET['doc_type']==Document::OUTGOING)
                    );
            
            $my = array(
                        'label'=>'Assegnati a me', 
                        'url'=>Yii::app()->createUrl('/document/my'), 
                        'visible'=>Yii::app()->authgateway->isAllowed('document', 'my'),
                        'active'=>$this->id=='document' && $this->action->id=='my'
                    );
            
            $owned = array(
                        'label'=>'Creati da me', 
                        'url'=>Yii::app()->createUrl('/document/owned'), 
                        'visible'=>Yii::app()->authgateway->isAllowed('document', 'owned'),
                        'active'=>$this->id=='document' && $this->action->id=='owned'
                    );            
            
            $disabled = array(
                        'label'=>'Cestino', 
                        'url'=>Yii::app()->createUrl('/document/disabled'), 
                        'visible'=>Yii::app()->authgateway->isAllowed('document', 'disabled'),
                        'active'=>$this->id=='document' && $this->action->id=='disabled'
            );            
            
            $pending = array(
                        'label'=>'In attesa', 
                        'url'=>Yii::app()->createUrl('/document/pending'), 
                        'visible'=>Yii::app()->authgateway->isAllowed('document', 'pending'),
                        'active'=>$this->id=='document' && $this->action->id=='pending'
                    );

            $tickets = array(
                        'label'=>'Gestisci Tickets', 
                        'url'=>Yii::app()->createUrl('/ticket'), 
                        'visible'=>Yii::app()->authgateway->isAllowed('ticket', 'index'),
                        'active'=>$this->id=='ticket' && $this->action->id=='index'
                    );
            
            $my_tickets = array(
                        'label'=>'Miei Tickets', 
                        'url'=>Yii::app()->createUrl('/ticket/my'), 
                        'visible'=>Yii::app()->authgateway->isAllowed('ticket', 'my'),
                        'active'=>$this->id=='ticket' && $this->action->id=='my'
                    );            
                        
            $senders = array(
                        'label'=>'Rubrica Mittenti', 
                        'url'=>Yii::app()->createUrl('/sender'), 
                        'visible'=>Yii::app()->authgateway->isAllowed('sender', 'index'),
                        'active'=>$this->id=='sender'
                    );
            
            // roles
            $roles = array(
                        'label'=>'Ruoli', 
                        'url'=>Yii::app()->createUrl('/role'), 
                        'visible'=>Yii::app()->authgateway->isAllowed('role','index'), 
                        'active'=>$this->id=='role'
                    );

            // groups
            $groups = array(
                        'label'=>'Gruppi', 
                        'url'=>Yii::app()->createUrl('/group'), 
                        'visible'=>Yii::app()->authgateway->isAllowed('group','index'),                 
                        'active'=>$this->id=='group'
                    );            
            
            // users
            $users = array(
                        'label'=>'Utenti', 
                        'url'=>Yii::app()->createUrl('/user'), 
                        'visible'=>Yii::app()->authgateway->isAllowed('user','index'),                 
                        'active'=>$this->id=='user'
                    );
            
            // rights
            $rights = array(
                        'label'=>'Permessi', 
                        'url'=>Yii::app()->createUrl('/right/'), 
                        'visible'=>Yii::app()->authgateway->isAllowed('right','index'),                 
                        'active'=>$this->id=='right'
                    );             
            // tags
            $tags = array(
                        'label'=>'Tag', 
                        'url'=>Yii::app()->createUrl('/tag/'), 
                        'visible'=>Yii::app()->authgateway->isAllowed('tag','index'),                                 
                        'active'=>$this->id=='tag'
                    );            
            
            // notifications
            $notifications = array(
                        'label'=>'Notifiche<span id="notifications_container"></span>', 
                        'url'=>Yii::app()->createUrl('/notification/'), 
                        'active'=>$this->id=='notification',
                        'encodeLabel'=>false
                    );
            
            // profile
            $profile = array(
                        'label'=>'Profilo', 
                        'url'=>Yii::app()->createUrl('/profile/edit'), 
                        'active'=>$this->id=='profile'
                    );

            $spending_list = array(
                        'label'=>'Spese', 
                        'url'=>Yii::app()->createUrl('/spending/search'), 
                        'active'=>$this->id=='spending' && $this->action->id=='search'
                    );
            
            $spending_owned = array(
                        'label'=>'Create da me', 
                        'url'=>Yii::app()->createUrl('/spending/owned'), 
                        'visible'=>Yii::app()->authgateway->isAllowed('spending', 'owned'),
                        'active'=>$this->id=='spending' && $this->action->id=='owned'
                    );            
            
            $spending_disabled = array(
                        'label'=>'Cestino', 
                        'url'=>Yii::app()->createUrl('/spending/disabled'), 
                        'visible'=>Yii::app()->authgateway->isAllowed('spending', 'disabled'),
                        'active'=>$this->id=='spending' && $this->action->id=='disabled'
            );            
            
            $items[] = $home;
            $items[] = array('label'=>'Cerca in', 'icon'=>'zoom-in', 'itemOptions'=>array('class'=>'nav-header'));
            $items[] = $archive_search;
            $items[] = $protocol_search;
            $items[] = $publish_search;
            $items[] = $spending_list; 
            
            $items[] = array('label'=>'Documenti', 'icon'=>'book', 'itemOptions'=>array('class'=>'nav-header'));
            $items[] = $pending;
            $items[] = $my;
            $items[] = $owned;
            $items[] = $disabled;
            
            $items[] = array('label'=>'Spese', 'icon'=>'shopping-cart', 'itemOptions'=>array('class'=>'nav-header'));
            $items[] = $spending_owned;
            $items[] = $spending_disabled;

            $items[] = array('label'=>'Amministrazione', 'icon'=>'tags', 'itemOptions'=>array('class'=>'nav-header'));            
            $items[] = $tickets;            
            $items[] = $my_tickets;
            $items[] = $tags;
            $items[] = $senders;

            if(Yii::app()->user->isAdmin())
            {
                $items[] = array('label'=>'Organizzazione', 'icon'=>'user', 'itemOptions'=>array('class'=>'nav-header'));
                $items[] = $roles;
                $items[] = $users;
                $items[] = $groups;
                $items[] = $rights;
            }            
            $items[] = array('label'=>'Area Privata', 'icon'=>'lock', 'itemOptions'=>array('class'=>'nav-header'));
            $items[] = $notifications;
            $items[] = $profile;
            $this->menu = $items;
        }
        
        protected function buildHMenu()
        {
            $items = array();
            $home = array('label'=>'Home', 'url'=>Yii::app()->homeUrl, 'active'=>($this->id=='site' && $this->action->id=='index'));
            $document = array('label'=>'Cerca in', 'url'=>'#', 'active'=>$this->id=='document', 'items'=>array(
                array('label' => 'Archivio', 'url' => Yii::app()->createUrl('/document/search', array('doc_type'=>Document::INTERNAL_USE_TYPE))),
                array('label' => 'Posta in entrata', 'url' => Yii::app()->createUrl('/document/search', array('doc_type'=>Document::INBOX))),                
                array('label' => 'Document pubblici', 'url' => Yii::app()->createUrl('/document/search', array('doc_type'=>Document::OUTGOING))),
                array('label' => 'Spese', 'url' => Yii::app()->createUrl('/spending/search'))                
            ));            

            $admin = array('label'=>'Amministrazione', 'url'=>Yii::app()->createUrl('/ticket'), 'active'=>in_array($this->id, array('ticket', 'tag', 'sender')));            
            $users = array('label'=>'Organizzazione', 'url'=>Yii::app()->createUrl('/user'), 'active'=>in_array($this->id, array('user', 'role', 'group', 'right')));            
            $items[] = $home;
            $items[] = $document;
            $items[] = $admin;
            if(Yii::app()->user->isAdmin())
                $items[] = $users;
            $this->h_menu = $items;
        }
        
        public function ajaxSuccess($message)
        {
            $ret = array(
                'success' => 1,
                'message'=>$message
            );
            echo CJSON::encode($ret);            
            Yii::app()->end();
        }
        
        public function ajaxError($errors = array())
        {
            $ret = array(
                'success' => 0,
                'errors'=>$errors
            );
            echo CJSON::encode($ret);
            Yii::app()->end();            
        }

        public function getMenu()
        {
            $this->buildMenu();
            return $this->menu;
        }
        
        public function getHMenu()
        {
            $this->buildHMenu();
            return $this->h_menu;
        }
        
        public function isAllowed($controller, $action)
        {
            return Yii::app()->authgateway->isAllowed($controller, $action);
        }
}