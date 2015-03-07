<?php

$this->create('registration.index', '/register/')
        ->get()
        ->action('OCA\Registration\Controller', 'index');
$this->create('registration.send.email', '/register/')
        ->post()
        ->action('OCA\Registration\Controller', 'sendEmail');
$this->create('registration.register.form', '/register/verify/{token}')
        ->get()
        ->action('OCA\Registration\Controller', 'registerForm');
$this->create('registration.create.account', '/register/verify/{token}')
        ->post()
        ->action('OCA\Registration\Controller', 'createAccount');
