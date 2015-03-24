<?php
	OCP\Util::addStyle('core', 'guest');
	$_['email'] = array_key_exists('email',$_) ? $_['email'] : '';
?>

<div id="login-pre-spacer"></div>
<div id="login-messaging">
<p class="main">Cloud storage you actually <span>control.</span></p>
</div>
<div id="login-spacer"></div>
<!--[if IE 8]><style>input[type="checkbox"]{padding:0;}</style><![endif]-->

<?php if ($_['entered']): ?>
	<?php if (empty($_['errormsg'])): ?>
		<div class="success" style="width:100%;"><p>
		<?php print_unescaped($l->t('Thank you for registering, you should receive an email with the verification link in a few minutes.')); ?>
		</p></div>
	<?php else: ?>
		<form action="<?php print_unescaped(OC_Helper::linkToRoute('registration.send.email')) ?>" method="post" id="regist">
			<fieldset>
				<div class="errors"><p>
				<?php print_unescaped($_['errormsg']); ?>
				</p></div>
				<p class='info'><?php print_unescaped($l->t('Please re-enter a valid email address')); ?></p><br/>

			<p class="infield grouptop">
				<input type="email" name="email" id="email" placeholder="<?php print_unescaped($l->t('Email')) ;?>" value="<?php echo $_['email']; ?>" required autofocus />
				<img style="top:1.4em;" class="svg" src="<?php print_unescaped(image_path('', 'actions/mail.svg')); ?>" alt=""/>
			</p>

			<p class="infield groupmiddle">
				<input type="email" name="email-clone" id="email-clone" placeholder="<?php print_unescaped($l->t('Re-type Email')) ;?>"/>
				<img style="top:1.0em;" class="svg" src="<?php print_unescaped(image_path('', 'actions/mail.svg')); ?>" alt=""/>
			</p>
			<br/>

			<div id="formMsgContainer" class="errors" style="display:none;">
				<p id="formMsg"></p>
			</div>

			<input type="submit" id="submit"  class="login primary" value="<?php print_unescaped($l->t('Request link')); ?>" />

			</fieldset>
		</form>
	<?php endif; ?>
<?php else: ?>
	<form action="<?php print_unescaped(OC_Helper::linkToRoute('registration.send.email')) ?>" method="post" id="regist">
		<fieldset>
			<?php if (!empty($_['errormsg'])): ?>
				<div class="errors"><p>
				<?php print_unescaped($_['errormsg']); ?>
				</p></div>
				<p class='info'><?php print_unescaped($l->t('Please enter a valid email address')); ?></p><br/>
			<?php else: ?>
				<p class='info'><?php print_unescaped($l->t('You will receive an email with a verification link')); ?></p><br/>
			<?php endif; ?>
			<p class="infield grouptop">
				<input type="email" name="email" id="email" placeholder="<?php print_unescaped($l->t('Email')) ;?>" value="<?php echo $_['email']; ?>" required autofocus />
				<img style="top:1.4em;" class="svg" src="<?php print_unescaped(image_path('', 'actions/mail.svg')); ?>" alt=""/>
			</p>

			<p class="infield groupmiddle">
				<input type="email" name="email-clone" id="email-clone" placeholder="<?php print_unescaped($l->t('Re-type Email')) ;?>"/>
				<img style="top:1.0em;" class="svg" src="<?php print_unescaped(image_path('', 'actions/mail.svg')); ?>" alt=""/>
			</p>
			<br/>

			<div id="formMsgContainer" class="errors" style="display:none;">
				<p id="formMsg"></p>
			</div>

			<input type="submit" id="submit"  class="login primary" value="<?php print_unescaped($l->t('Request link')); ?>" />

		</fieldset>
	</form>
<?php endif; ?>

<?php OC_Util::addScript("registration", "register");?>

