<?php
return array(
    'components'=>array(
        //Database of Magento1
        'mage1' => array(
            'connectionString' => 'mysql:host=localhost;dbname=wvodesig_mage1_dev_ver17',
            'emulatePrepare' => true,
            'username' => 'wvodesig_dev17',
            'password' => 'DebutLamasUponSushi',
            'charset' => 'utf8',
            'tablePrefix' => 'mage_',
            'class' => 'CDbConnection'
        ),
        //Database of Magento2 beta
        'mage2' => array(
            'connectionString' => 'mysql:host=localhost;dbname=wvodesig_mag2wvo',
            'emulatePrepare' => true,
            'username' => 'wvodesig_mag2wvo',
            'password' => 'q1w2e3r4@123456',
            'charset' => 'utf8',
            'tablePrefix' => '',
            'class' => 'CDbConnection'
        )
    ),

    'import'=>array(
        //This can change for your magento1 version if needed
        //'application.models.db.mage19x.*',
        'application.models.db.mage19x.*',
    )
);
