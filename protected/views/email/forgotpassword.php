<html>
<body>
<h1>Procedura di recupero password</h1>
<p>E' stata attivata la procedura per il recupero della password sul sito <?php echo Yii::app()->createAbsoluteUrl(Yii::app()->homeUrl) ?> per l'utente: <?php echo $user->email?></p>
<p>Per reimpostare la password clicca sul seguente link o incollalo sulla barra degli indirizzi del tuo browser:<br/>
<a href="<?php echo Yii::app()->createAbsoluteUrl('/auth/resetpassword', array('email'=>$user->email, 'new_password_key'=>$user->new_password_key))?>" title="Reimposta Password" ><?php echo Yii::app()->createAbsoluteUrl('/auth/resetpassword', array('email'=>$user->email, 'new_password_key'=>$user->new_password_key))?></a>
</p>
</body>
</html>