<?php

Yii::setAlias('@FileStorage', realpath(dirname(__FILE__).'/../fileStorage/'));

return [
    'adminEmail' => 'admin@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',
    'catalog_root_title' => 'Заголовок корневого раздела каталога',
    'catalog_root_description' => 'Описание корневого раздела каталога',
    'jwt' => [
        'issuer' => 'http://2uit.local',
        'audience' => 'http://2uit.local',
        'id' => 'jxqboMMMae|rg~fKUoOqh7B7zQpC0b%|#JMQni%U51Tlntqn}PafueHT5Cq5Xu}Z',
        'expire' => '+1 hour',
        'request_time' => 'now',
    ],
    'origins' => [ //список допустимых Origin для CORS при расположении фронта и бэка на разных доменах
        'http://localhost',
        'http://localhost:8080',
        'http://127.0.0.1',
        'http://127.0.0.1:8080',
    ],
    'breakCors' => false, //переключение в true сломает CORS. Cross-origin запросы будут работать для всех доменов.
    'passwordSalt' => 'jTW}NPqxJ$5ey#rhxiHMG?lHaZgVO?sm', //соль для генерации хэша пароля.
    'environment' => 'dev', //установить prod на продакшене перед выполнением миграций!!!

];
