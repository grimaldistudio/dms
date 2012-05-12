<!DOCTYPE html>
<html lang="it">
  <head>
    <meta charset="utf-8">
    <title><?php echo CHtml::encode($this->pageTitle); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Document Management System">
    <meta name="author" content="Engineering Solution (fabrizio.dammassa@gmail.com)">

    <style type="text/css">
      body {
        padding-top: 60px;
        padding-bottom: 40px;
      }
      .sidebar-nav {
        padding: 9px 0;
      }
    </style>
    
    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!-- Le fav and touch icons -->
    <link rel="shortcut icon" href="<?php echo Yii::app()->request->baseUrl; ?>/ico/favicon.ico">
    <link rel="apple-touch-icon" href="<?php echo Yii::app()->request->baseUrl; ?>/ico/bootstrap-apple-57x57.png">
    <link rel="apple-touch-icon" sizes="72x72" href="<?php echo Yii::app()->request->baseUrl; ?>/ico/bootstrap-apple-72x72.png">
    <link rel="apple-touch-icon" sizes="114x114" href="<?php echo Yii::app()->request->baseUrl; ?>/ico/bootstrap-apple-114x114.png">
    <?php Yii::app()->clientScript->registerPackage('jqueryi'); ?>
    <?php Yii::app()->clientScript->registerPackage('jquery-ui'); ?>
    <?php Yii::app()->clientScript->registerCssFile('/css/dms.css'); ?>
  </head>

  <body>

    <?php 
    $right_link = "";
    if(Yii::app()->user->isLoggedIn())
    {
        $right_link = CHtml::link(Yii::app()->user->name, array('/profile/edit'));
        $right_link .= CHtml::link("Logout", array('/auth/logout'));
    }
    else
        $right_link = CHtml::link('Login', array('/auth/login'));
    ?>
      
    <?php
        $upload_document_item = '';
        if($this->isAllowed('document', 'upload'))
        {
            $upload_document_item = CHtml::link('<i class="icon-plus icon-white"></i>', '#', array('class'=>'btn btn-primary upload-new-document', 'rel'=>'tooltip', 'title'=>'Carica Nuovo Documento'));
        }
    ?>
    <?php $this->widget('bootstrap.widgets.BootNavbar', array(
        'fixed'=>true,
        'brand'=>Yii::app()->name,
        'brandUrl'=>false,
        'collapse'=>true,
        'fluid'=>true,
        'items'=>array(
            array(
                'class'=>'bootstrap.widgets.BootMenu',
                'items'=>$this->getHMenu()
            ),
            array(
                'class'=>'bootstrap.widgets.BootMenu',
                'htmlOptions' => array('class'=>'pull-right'),
                'items'=>array(
                    array('label'=>'Login', 'url'=>Yii::app()->createUrl('/auth/login'), 'visible'=>Yii::app()->user->getIsGuest())
                ),
            ),
            $upload_document_item,
            array(
                'class'=>'bootstrap.widgets.BootMenu',
                'htmlOptions' => array('class'=>'pull-right'),
                'items'=>array(
                    array('label'=>'<span class="notifications_container">0</span>', 
                          'items'=> array(
                              array(
                                    'label'=>'<div class="notifications"><span id="notification_content">Nessuna notifica</span></div>',
                                )
                            ),
                          'visible'=>$this->isAllowed('notification', 'index')
                    ),                    
                    array('label'=>Yii::app()->user->name, 
                          'items'=> array(
                              array('label'=>'Modifica Profilo', 'url'=>Yii::app()->createUrl('/profile/edit'), 'visible'=>Yii::app()->user->isLoggedIn()),
                              array('label'=>'Logout', 'url'=>Yii::app()->createUrl('/auth/logout'), 'visible'=>Yii::app()->user->isLoggedIn()),
                          ),
                          'visible'=>Yii::app()->user->isLoggedIn()
                    )
                    
                ),
                'encodeLabel'=>false
            ),
        )
        
    )); ?>

    <div class="container-fluid">
      <div class="row-fluid">
        <div class="span3">
          <div class="well sidebar-nav">
            <?php $this->widget('bootstrap.widgets.BootMenu', array(
                'type' => 'list',
                'items' => $this->getMenu(),
            ));
            ?>
          </div><!--/.well -->
        </div><!--/span-->
        <div class="span9">
            <?php $this->widget('bootstrap.widgets.BootCrumb', $this->breadcrumbs);?>
            <?php $this->widget('bootstrap.widgets.BootAlert'); ?>            
            <?php echo $content; ?>
            <?php echo $this->renderPartial('/document/_upload', array('model'=>new UploadDocumentForm())) ?>
        </div><!--/span-->
      </div><!--/row-->

      <hr>

      <footer>
        <p>&copy; Engineering Solution 2012</p>
      </footer>
      <?php 
        Yii::app()->clientScript->registerScript('notifications-loader', "
            loadNotifications('".Yii::app()->createUrl('/notification/new')."', '".Yii::app()->createUrl('/notification/index')."');
        "); 
      ?>
    </div><!--/.fluid-container-->
  </body>
</html>
