REQUIREMENTS
------------

PHP 8.1+, всё, что стандартно требуется для Yii2.<br>
MySQL 8+ или MariaDB 10.5+<br>

INSTALLATION
------------
Первым делом, клонируйте проект и установите зависимости композером
~~~
git clone git@github.com:Ulfhednur/2uit_test-task.git /каталог/куда/удобно
cd /каталог/куда/удобно
composer install
composer update
~~~
в /config/db.php исправьте на соответствующие реквизиты базы данных
```php
    'dsn' => 'mysql:host=localhost;dbname=tts_2uit',
    'username' => 'test_tasks',
    'password' => 'test_password',
```
в /config/params.php исправьте issuer и audience на свой домен
```php
    'jwt' => [
        'issuer' => 'http://2uit.local',
        'audience' => 'http://2uit.local',
```
Далее:
~~~
php yii migrate/up
php yii user/create login password
~~~
В итоге вы получите установленное приложение и пользователя с реквизитами login/password<br>

Методы API
------------
Все методы передаются с заголовками 
```angular2html
Content-Type: application/json
Accept-Encoding: application/json
```
### POST /auth/login
Возвращает токен авторизации.<br>
Тело запроса
```json
{
  "username":"admin",
  "password":"admin"
}
```
Пример ответа
```json
{
  "username": "admin",
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vMnVpdC5sb2NhbCIsImF1ZCI6Imh0dHA6Ly8ydWl0LmxvY2FsIiwianRpIjoianhxYm9NTU1hZXxyZ35mS1VvT3FoN0I3elFwQzBiJXwjSk1RbmklVTUxVGxudHFufVBhZnVlSFQ1Q3E1WHV9WiIsImlhdCI6MTcwMjYxODMwNy41MDMzMywibmJmIjoxNzAyNjE4MzA3LjUwMzMzLCJleHAiOjE3MDI2MjE5MDcuNTAzMzMsInVpZCI6MX0.V7-FEoDSWMD7_p7sVT6D-iit6Ia9uPnvlVwo_BdUrok"
}
```
### POST /items
Добавляет запись.<br>
Авторизация: заголовок
```angular2html
Authorization: Bearer <токен из /auth/login>
```
Тело запроса
```json
{
  "fio":"Иванов Александр Иванович",
  "email":"mail@example.com",
  "phone":"+7 (925) 123-45-67",
  "storage":"database"
}
```
Пример ответа
```json
{
  "status": "ok"
}
```
### PUT /items
Находит запись в хранилище и обновляет её.<br>
Авторизация: заголовок
```angular2html
Authorization: Bearer <токен из /auth/login>
```
Тело запроса
```json
{
  "fio":"Иванов Александр Иванович",
  "email":"mail@example.com",
  "phone":"+7 (925) 123-45-67"
}
```
Пример ответа
```json
{
  "status": "ok"
}
```
### GET /items?storage=\<storageName>
Находит запись в хранилище и обновляет её.<br>
Авторизация: не требуется
Допустимые значения \<storageName>:
 * database
 * cache
 * json
 * xlsx
<p>Пример ответа</p>

```json
[
  {
    "fio": "Иванов Александр Иванович",
    "email": "mail@example.com",
    "phone": "+79251234567"
  },
  {
    "fio": "Иванов Иван Иванович",
    "email": "mail@example.com",
    "phone": "+79251234567"
  }
]
```
