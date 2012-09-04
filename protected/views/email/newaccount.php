<html>
<body>
<h1>Benvenuto su <?php echo Yii::app()->name; ?></h1>
<p>E' stato creato un account al seguente indirizzo: <?php echo Yii::app()->createAbsoluteUrl(Yii::app()->homeUrl) ?></p>
<p>Per accedere immetti le informazioni di login che seguono al link : 
	<a href="<?php echo Yii::app()->createAbsoluteUrl(Yii::app()->user->loginUrl) ?>" ><?php echo Yii::app()->createAbsoluteUrl(Yii::app()->user->loginUrl) ?></a>:</p>
	<p>
		Nome utente: <?php echo $user->email; ?><br/>
		Password: <?php echo $user->plain_password; ?>
	</p>
</body>
</html>