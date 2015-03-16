
	
<ul>
	<?php 
	 if (array_key_exists('errors',$_) && $_['errors']){
		foreach($_["errors"] as $error){
			echo "<li class='error'>";
				$error['error'];
				
			echo "<br/>	<p class='hint'>";
			if(isset($error['hint']))print_unescaped($error['hint']);
			echo " </p></li>";
		}
	}
	?>
	

	
	<?php if($_['success']): ?>
		<li class='success' style="width:100%;" >
			<h1><?php echo $l->t('Your account was created!'); ?></h1>
		</li>
		
		<p class='info'>
				<a class="hint" href="<?php echo OC_Helper::linkTo('', 'index.php') ?>/"><?php echo $l->t('Go to login page'); ?></a>
		</p>
	<?php endif; ?>
	
</ul>
