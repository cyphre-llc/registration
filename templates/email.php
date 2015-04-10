<?php
 $mailTheme = $theme->getMailTheme();
?>
<html><body>
<table style="background-color:<?php print_unescaped($mailTheme['wrapperBgColor']); ?>;font-family:<?php print_unescaped($mailTheme['fontFace']); ?>;font-size:<?php print_unescaped($mailTheme['fontSize']); ?>;line-height:<?php print_unescaped($mailTheme['lineHeight']); ?>;font-weight:normal;width:840px;"><tbody>
<tr><td colspan="3"><br></td></tr>
<tr><td colspan="3" align="center">
<a style="text-decoration:none;" href="<?php p($theme->getBaseUrl()); ?>">
<img height="44" alt="cyphre.com" src="<?php print_unescaped($mailTheme['logo']); ?>">
</a>
</td></tr>
<tr><td colspan="3"><br></td></tr>
<tr><td width="70px;"></td>
<td><table style="background-color:<?php print_unescaped($mailTheme['contentBgColor']);?>;color:<?php print_unescaped($mailTheme['contentColor']);?>;width:100%"><tbody>
<tr><td width="20px;"></td>
<td style="text-align:left;min-width:600px;<?php print_unescaped($mailTheme['contentBgColor']);?>;">
<br>
<?php print_unescaped($l->t('Hi there!')); ?>
<br><br>
<?php print_unescaped($l->t('To complete your Cyphre account registration, please confirm your email address by clicking')); ?>
 <a style="text-decoration:none;" href="<?php print_unescaped($_['link']); ?>"><?php print_unescaped($l->t('here')); ?></a>.
<br><br><br>
<?php print_unescaped($l->t('Cheers,<br>Team Cyphre')); ?>
<br><br>
</td>
<td width="20px;"></td></tr>
<tr><td colspan="3"><br><br></td></tr>
</tbody></table></td>
<td width="70px;"></td></tr>
<tr><td colspan="3"><br><br></td></tr>
<tr><td width="70px;"></td>
<td><table style="background-color:<?php print_unescaped($mailTheme['contentBgColor']);?>;color:<?php print_unescaped($mailTheme['contentColor']);?>;width:100%"><tbody>
<tr><td colspan="3"><br></td></tr>
<tr><td width="20px;"></td>
<td style="text-align:center;min-width:600px;">
<?php print_unescaped($l->t('We would love to hear from you. To send us feedback simply email us at')); ?>
<br>
<a style="text-decoration:none;" href="mailto:support@cyphre.com" target="_blank">support@cyphre.com</a> <?php print_unescaped($l->t('or find us on')); ?> 
<a style="text-decoration:none;" href="https://www.facebook.com/pages/Cyphre/424876137665559">Facebook</a>
 <?php print_unescaped($l->t('or')); ?> <a style="text-decoration:none;" href="https://twitter.com/GetCyphre">Twitter</a>.
</td>
<td width="20px;"></td></tr>
<tr><td colspan="3"><br></td></tr>
</tbody></table></td>
<td width="70px;"></td></tr>
<tr><td colspan="3"><br><br><br></td></tr>
</tbody></table>
