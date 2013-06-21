<?php
if (!defined ('TYPO3_MODE'))     die ('Access denied.');

t3lib_extMgm::addService($_EXTKEY,  'auth' /* sv type */,  'tx_cabemailuserauth_sv1' /* sv key */,
        array(

            'title' => 'E-mail and Username fe_user authentication',
            'description' => 'Allows fe_users to login with their username or e-mail address',

            'subtype' => 'getUserFE,authUserFE',

            'available' => TRUE,
            'priority' => 10,
            'quality' => 50,

            'os' => '',
            'exec' => '',

            'classFile' => t3lib_extMgm::extPath($_EXTKEY).'sv1/class.tx_cabemailuserauth_sv1.php',
            'className' => 'tx_cabemailuserauth_sv1',
        )
    );
?>