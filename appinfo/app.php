<?php

\OCP\Util::connectHook('\OC\User', 'postCreateUser', '\OCA\Registration\Controller', 'postCreateUser');
\OCP\Util::connectHook('\OC\User', 'postDelete', '\OCA\Registration\Controller', 'postDelete');
