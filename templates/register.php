<?php \OCP\Util::addStyle('core', 'guest'); ?>

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
		<form action="<?php print_unescaped(OC_Helper::linkToRoute('registration.send.email')) ?>" method="post">
			<fieldset>
				<div class="errors"><p>
				<?php print_unescaped($_['errormsg']); ?>
				</p></div>
				<p class="infield" style="position:absolute;">
					<input style="padding-left: 1.8em;" type="email" name="email" id="email" placeholder="" value="" required autofocus />
					<label for="email" class="infield"><?php print_unescaped($l->t( 'Email' )); ?></label>
					<img style="position:absolute; left:1.25em; top:1.65em;-ms-filter:'progid:DXImageTransform.Microsoft.Alpha(Opacity=30)'; filter:alpha(opacity=30); opacity:.3;" class="svg" src="<?php print_unescaped(image_path('', 'actions/mail.svg')); ?>" alt=""/>
                                        <br>
				        <input type="submit" id="submit" class="login primary" value="<?php print_unescaped($l->t('Request link')); ?>" />
				</p>
			</fieldset>
		</form>
	<?php endif; ?>
<?php else: ?>
	<form action="<?php print_unescaped(OC_Helper::linkToRoute('registration.send.email')) ?>" method="post">
		<fieldset>
			<?php if (!empty($_['errormsg'])): ?>
				<div class="errors"><p>
				<?php print_unescaped($_['errormsg']); ?>
				</p></div>
				<p class='info'><?php print_unescaped($l->t('Please re-enter a valid email address')); ?></p>
			<?php else: ?>
				<p class='info'><?php print_unescaped($l->t('You will receive an email with a verification link')); ?></p>
			<?php endif; ?>
			<p class="infield" style="position:absolute;">
				<input style="width: 11.7em;	padding-left: 1.8em;"  type="email" name="email" id="email" placeholder="" value="" required autofocus />
				<label for="email" class="infield"><?php print_unescaped($l->t( 'Email' )); ?></label>
				<img style="position:absolute; left:1.25em; top:1.65em;-ms-filter:'progid:DXImageTransform.Microsoft.Alpha(Opacity=30)'; filter:alpha(opacity=30); opacity:.3;" class="svg" src="<?php print_unescaped(image_path('', 'actions/mail.svg')); ?>" alt=""/>
			        <br>
			        <input type="submit" id="submit"  class="login primary" style="" value="<?php print_unescaped($l->t('Request link')); ?>" />
		        </p>
		</fieldset>
	</form>
<?php endif; ?>

