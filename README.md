# Laravel Task Management REST API

> **Laravel-based RESTful API for collaborative task and team management with Sanctum authentication**

[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat-square&logo=php)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=flat-square&logo=laravel)](https://laravel.com)
[![License](https://img.shields.io/badge/License-MIT-green.svg?style=flat-square)](LICENSE)
[![PHPUnit](https://img.shields.io/badge/Tests-PHPUnit-4F5B93?style=flat-square&logo=php)](https://phpunit.de/)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?style=flat-square&logo=docker)](https://www.docker.com/)

## Disclaimer

### Dockerfile
 
[Dockerfile](Dockerfile)

<details>
  <summary>################################### - EXPAND CODE BLOCK - #####################################</summary>

```dockerfile
FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    procps \
    net-tools \
    lsof \
    libjpeg-dev \
    libfreetype6-dev \
    git \
    curl \
    && docker-php-ext-install mbstring exif pcntl bcmath gd pdo pdo_mysql zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN pecl install redis && docker-php-ext-enable redis \
    && pecl install xdebug && docker-php-ext-enable xdebug

RUN docker-php-ext-install mbstring exif pcntl bcmath gd pdo pdo_mysql zip

#COPY ./nginx/php.ini /usr/local/etc/php/conf.d/custom-php.ini
#COPY ./nginx/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

COPY . /var/www

WORKDIR /var/www

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache && \
    chmod -R 775 /var/www/storage /var/www/bootstrap/cache

RUN composer install --no-interaction --prefer-dist --optimize-autoloader

USER www-data

CMD ["php-fpm"]

```
</details>

Ready for build.

I've commented out the configurations for running the container locally.
```yaml
# COPY ./nginx/php.ini /usr/local/etc/php/conf.d/custom-php.ini
# COPY ./nginx/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
```

Uncomment them if you need to build an independent image
```yaml
COPY ./nginx/php.ini /usr/local/etc/php/conf.d/custom-php.ini
COPY ./nginx/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
```

### docker-compose.yml
[docker-compose.yml](docker-compose.yml)

Oriented towards local deployment, using volumes for that purpose.
```yaml
volumes:
    - .:/var/www # for the ability to locally plug-and-play modify project files for development
    - ./nginx/default.conf:/etc/nginx/conf.d/default.conf
    - ./nginx/snippets/fastcgi-php.conf:/etc/nginx/snippets/fastcgi-php.conf
```

All the necessary hosts for connecting to containers are specified in the environment files as well as in the Docker configurations. Everything is set to the default settings for simplicity.

## Run app localy
1. Clone the repository to your local project folder.
2. Check the volumes and config forwarding. Choose the one you prefer. For more information, see [Disclaimer](#disclaimer).
3. `docker-compose up --build -d` just use to run App

The application API should work at the address `http://localhost:8000`

`http:\\localhost:8000` By default it is set to the standard Laravel web stub

## Application composition (Services)
```
NAME      IMAGE          SERVICE   STATUS       PORTS
mysql     mysql:8.0.33   mysql     Up 2 hours   0.0.0.0:3306->3306/tcp, 33060/tcp
nginx     nginx:latest   nginx     Up 2 hours   0.0.0.0:8000->80/tcp
php       tasks-php      php       Up 2 hours   9000/tcp
redis     redis:7.0.7    redis     Up 2 hours   0.0.0.0:6379->6379/tcp
```


## Routes of group `api/v1`
```php
Route::prefix('v1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
   
        Route::get('/tasks', [TaskController::class, 'index']);
        Route::post('/tasks', [TaskController::class, 'store']);
        Route::get('/tasks/{task}', [TaskController::class, 'show']);
        Route::put('/tasks/{task}', [TaskController::class, 'update']);
        Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);
    
        Route::post('/tasks/{taskId}/comments', [CommentController::class, 'store']);
        Route::get('/tasks/{taskId}/comments', [CommentController::class, 'index']);
        Route::delete('/comments/{id}', [CommentController::class, 'destroy']);
    
        Route::get('/teams', [TeamController::class, 'index']);
        Route::post('/teams', [TeamController::class, 'store']);
        Route::get('/teams/{team}', [TeamController::class, 'show']);
        Route::put('/teams/{team}', [TeamController::class, 'update']);
        Route::delete('/teams/{team}', [TeamController::class, 'destroy']);
        Route::post('/teams/{team}/users', [TeamController::class, 'addUser']);
        Route::delete('/teams/{team}/users/{user}', [TeamController::class, 'removeUser']);
    });
});
```

## Authentication is based on the Sanctum library
In the future, it is expected that the logic of use will be expanded with the distribution of rights and
credentials for issued tokens

---

### Request example `http://localhost:8000/api/v1/register`
<details>
  <summary>################################### - EXPAND CODE BLOCK - #####################################</summary>

```php
POST http://localhost:8000/api/v1/register
Content-Type: application/json

{
  "name": "MYNAME",
  "email": "admin@admin.com",
  "password": "content123"
}

HTTP/1.1 201 Created
Server: nginx/1.27.1
Content-Type: application/json
Transfer-Encoding: chunked
Connection: keep-alive
X-Powered-By: PHP/8.2.23
Cache-Control: no-cache, private
Date: Tue, 03 Sep 2024 16:23:34 GMT
Access-Control-Allow-Origin: *


Response {
  "access_token": "2|4BrtGfUhacVxSSMFYKiaX6LMmUuRQu7pxrm8aUXY2ac15ad4",
  "token_type": "Bearer"
}
Response code: 201 (Created); Time: 4942ms (4 s 942 ms); Content length: 91 bytes (91 B)
```
</details>

### Request example `http://localhost:8000/api/v1/login`
<details>
  <summary>################################### - EXPAND CODE BLOCK - #####################################</summary>

```php
POST http://localhost:8000/api/v1/login
Content-Type: application/json

{
  "email": "admin@admin.com",
  "password": "content123"
}

HTTP/1.1 200 OK
Server: nginx/1.27.1
Content-Type: application/json
Transfer-Encoding: chunked
Connection: keep-alive
X-Powered-By: PHP/8.2.23
Cache-Control: no-cache, private
Date: Tue, 03 Sep 2024 16:23:40 GMT
Access-Control-Allow-Origin: *


Response {
  "access_token": "3|C2GBg1c3tAyJR9y1chCIgwT6m1CtOk33rkmJ5gmnbd5e3807",
  "token_type": "Bearer"
}

Response code: 200 (OK); Time: 4533ms (4 s 533 ms); Content length: 91 bytes (91 B)
```
</details>

### Request example `http://localhost:8000/api/v1/tasks`

<details>
  <summary>################################### - EXPAND CODE BLOCK - #####################################</summary>

```php
GET http://localhost:8000/api/v1/tasks
Authorization: Bearer 3|C2GBg1c3tAyJR9y1chCIgwT6m1CtOk33rkmJ5gmnbd5e3807

HTTP/1.1 200 OK
Server: nginx/1.27.1
Content-Type: application/json
Transfer-Encoding: chunked
Connection: keep-alive
X-Powered-By: PHP/8.2.23
Cache-Control: no-cache, private
Date: Tue, 03 Sep 2024 16:23:42 GMT
Access-Control-Allow-Origin: *


Response [
  {
    "id": 1,
    "created_at": "2024-09-03T15:33:25.000000Z",
    "updated_at": "2024-09-03T15:33:25.000000Z",
    "title": "Initial Task",
    "description": "Initial description",
    "status": "pending",
    "user_id": 1,
    "team_id": null
  }
]

Response code: 200 (OK); Time: 2151ms (2 s 151 ms); Content length: 201 bytes (201 B)
```
</details>

## Another Examples of requests to a group `api/v1`

```
http://localhost:8000/api/v1/tasks/15
http://localhost:8000/api/v1/tasks/15/comments
http://localhost:8000/api/v1/comments/55
http://localhost:8000/api/v1/teams
http://localhost:8000/api/v1/teams/78/users
http://localhost:8000/api/v1/teams/78/users/2
```

---
## Tests
- Runs inside the php container
- Login to php container `docker exec -it php /bin/bash`

### UnitTest
- For run directory `./storage/coverage-report` must be writeable on you local host machine
- `vendor/bin/phpunit --coverage-text --colors=always --testdox` run tests
- Open `storage/coverage-report/index.html` for detail analyze reports
---
#### Output example UnitTest

<details>
  <summary>################################### - EXPAND CODE BLOCK - #####################################</summary>

```shell
www-data@2999fbe3af46:~$ vendor/bin/phpunit --coverage-text --colors=always --testdox
PHPUnit 11.3.1 by Sebastian Bergmann and contributors.

Runtime:       PHP 8.2.23 with Xdebug 3.3.2
Configuration: /var/www/phpunit.xml

.......................                                           23 / 23 (100%)

Time: 00:22.536, Memory: 42.50 MB

Auth (Tests\Feature\Auth)
 ✔ Registration
 ✔ Login
 ✔ Logout

Comment (Tests\Feature\Comment)
 ✔ Store comment
 ✔ Delete comment

Comment (Tests\Unit\Comment)
 ✔ Comment creation
 ✔ Comment relations

Task (Tests\Feature\Task)
 ✔ Store task
 ✔ Store and update task
 ✔ Show task
 ✔ Index tasks
 ✔ Delete task

Task (Tests\Unit\Task)
 ✔ Task creation
 ✔ Task relations

Team (Tests\Feature\Team)
 ✔ Store team
 ✔ Update team
 ✔ Index teams
 ✔ Add user to team
 ✔ Remove user from team
 ✔ Show team
 ✔ Delete team

Team (Tests\Unit\Team)
 ✔ Team creation and relations

User (Tests\Unit\User)
 ✔ User creation

OK (23 tests, 125 assertions)

Generating code coverage report in HTML format ... done [00:06.168]


Code Coverage Report:      
  2024-09-03 16:43:17      
                           
 Summary:                  
  Classes: 38.89% (7/18)   
  Methods: 59.30% (51/86)  
  Lines:   65.31% (160/245)

App\Http\Controllers\Api\AuthController
  Methods:  25.00% ( 1/ 4)   Lines:  68.00% ( 17/ 25)
App\Http\Controllers\Api\CommentController
  Methods:  16.67% ( 1/ 6)   Lines:  36.67% ( 11/ 30)
App\Http\Controllers\Api\TaskController
  Methods:  16.67% ( 1/ 6)   Lines:  67.44% ( 29/ 43)
App\Http\Controllers\Api\TeamController
  Methods:  12.50% ( 1/ 8)   Lines:  61.11% ( 22/ 36)
App\Models\Comment
  Methods: 100.00% ( 2/ 2)   Lines: 100.00% (  2/  2)
App\Models\Task
  Methods:  33.33% ( 1/ 3)   Lines:  33.33% (  1/  3)
App\Models\Team
  Methods:  50.00% ( 1/ 2)   Lines:  50.00% (  1/  2)
App\Models\User
  Methods: 100.00% ( 1/ 1)   Lines: 100.00% (  3/  3)
App\Providers\AppServiceProvider
  Methods: 100.00% ( 2/ 2)   Lines: 100.00% ( 21/ 21)
App\Repositories\EloquentCommentRepository
  Methods:  60.00% ( 3/ 5)   Lines:  50.00% (  3/  6)
App\Repositories\EloquentTaskRepository
  Methods: 100.00% ( 5/ 5)   Lines: 100.00% (  6/  6)
App\Repositories\EloquentTeamRepository
  Methods: 100.00% ( 7/ 7)   Lines: 100.00% ( 12/ 12)
App\Repositories\EloquentUserRepository
  Methods:  50.00% ( 3/ 6)   Lines:  38.46% (  5/ 13)
App\Services\CommentService
  Methods:  66.67% ( 4/ 6)   Lines:  62.50% (  5/  8)
App\Services\TaskService
  Methods: 100.00% ( 6/ 6)   Lines: 100.00% (  8/  8)
App\Services\TeamService
  Methods: 100.00% ( 8/ 8)   Lines: 100.00% ( 10/ 10)
App\Services\UserService
  Methods:  57.14% ( 4/ 7)   Lines:  50.00% (  4/  8)

```
</details>

---

### FeatureTest (E2E)
- Use `php artisan test` or for run default laravel test handler
- OR `php artisan test --filter=TaskTest` For run tests partly and pointary

#### Output example FeatureTest

<details>
  <summary>################################### - EXPAND CODE BLOCK - #####################################</summary>

```shell
www-data@2999fbe3af46:~$ php artisan test

   PASS  Tests\Unit\CommentTest
  ✓ comment creation                                                                                                                                                                                                                                     7.09s  
  ✓ comment relations                                                                                                                                                                                                                                    0.97s  

   PASS  Tests\Unit\TaskTest
  ✓ task creation                                                                                                                                                                                                                                        0.63s  
  ✓ task relations                                                                                                                                                                                                                                       0.56s  

   PASS  Tests\Unit\TeamTest
  ✓ team creation and relations                                                                                                                                                                                                                          0.60s  

   PASS  Tests\Unit\UserTest
  ✓ user creation                                                                                                                                                                                                                                        0.57s  

   PASS  Tests\Feature\AuthTest
  ✓ registration                                                                                                                                                                                                                                         1.59s  
  ✓ login                                                                                                                                                                                                                                                0.73s  
  ✓ logout                                                                                                                                                                                                                                               0.70s  

   PASS  Tests\Feature\CommentTest
  ✓ store comment                                                                                                                                                                                                                                        0.72s  
  ✓ delete comment                                                                                                                                                                                                                                       0.57s  

   PASS  Tests\Feature\TaskTest
  ✓ store task                                                                                                                                                                                                                                           0.73s  
  ✓ store and update task                                                                                                                                                                                                                                0.63s  
  ✓ show task                                                                                                                                                                                                                                            0.57s  
  ✓ index tasks                                                                                                                                                                                                                                          0.58s  
  ✓ delete task                                                                                                                                                                                                                                          0.61s  

   PASS  Tests\Feature\TeamTest
  ✓ store team                                                                                                                                                                                                                                           0.71s  
  ✓ update team                                                                                                                                                                                                                                          0.61s  
  ✓ index teams                                                                                                                                                                                                                                          0.58s  
  ✓ add user to team                                                                                                                                                                                                                                     0.58s  
  ✓ remove user from team                                                                                                                                                                                                                                0.63s  
  ✓ show team                                                                                                                                                                                                                                            0.60s  
  ✓ delete team                                                                                                                                                                                                                                          0.61s  

  Tests:    23 passed (125 assertions)
  Duration: 23.89s
```
</details>


## File structure diagram

<details>
  <summary>################################### - EXPAND CODE BLOCK - #####################################</summary>

```yaml
├── .dockerignore
├── .editorconfig
├── .env
├── .env.example
├── .gitattributes
├── .gitignore
├── .phpunit.result.cache
├── Dockerfile
├── README.md
├── app/
│   ├── Contracts/
│   │   ├── CommentRepositoryInterface.php
│   │   ├── CommentServiceInterface.php
│   │   ├── TaskRepositoryInterface.php
│   │   ├── TaskServiceInterface.php
│   │   ├── TeamRepositoryInterface.php
│   │   ├── TeamServiceInterface.php
│   │   ├── TokenRepositoryInterface.php
│   │   ├── TokenServiceInterface.php
│   │   ├── UserRepositoryInterface.php
│   │   └── UserServiceInterface.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   ├── AuthController.php
│   │   │   │   ├── CommentController.php
│   │   │   │   ├── TaskController.php
│   │   │   │   └── TeamController.php
│   │   │   └── Controller.php
│   ├── Models/
│   │   ├── Comment.php
│   │   ├── RefreshToken.php
│   │   ├── Task.php
│   │   ├── Team.php
│   │   └── User.php
│   ├── Providers/
│   │   ├── AppServiceProvider.php
│   │   └── RouteServiceProvider.php
│   ├── Repositories/
│   │   ├── EloquentCommentRepository.php
│   │   ├── EloquentTaskRepository.php
│   │   ├── EloquentTeamRepository.php
│   │   ├── EloquentTokenRepository.php
│   │   └── EloquentUserRepository.php
│   ├── Services/
│   │   ├── CommentService.php
│   │   ├── TaskService.php
│   │   ├── TeamService.php
│   │   ├── TokenService.php
│   │   └── UserService.php
├── artisan
├── bootstrap/
│   ├── app.php
│   ├── cache/
│   │   ├── .gitignore
│   │   ├── packages.php
│   │   └── services.php
│   └── providers.php
├── composer.json
├── composer.lock
├── config/
│   ├── app.php
│   ├── auth.php
│   ├── cache.php
│   ├── database.php
│   ├── filesystems.php
│   ├── logging.php
│   ├── mail.php
│   ├── queue.php
│   ├── sanctum.php
│   ├── services.php
│   └── session.php
├── database/
│   ├── .gitignore
│   ├── factories/
│   │   ├── CommentFactory.php
│   │   ├── TaskFactory.php
│   │   ├── TeamFactory.php
│   │   └── UserFactory.php
│   ├── migrations/
│   │   ├── 2024_08_30_121349_create_users_table.php
│   │   ├── 2024_08_30_121417_create_password_resets_table.php
│   │   ├── 2024_08_30_121449_create_personal_access_tokens_table.php
│   │   ├── 2024_08_30_122958_create_teams_table.php
│   │   ├── 2024_08_30_123121_create_tasks_table.php
│   │   ├── 2024_08_30_123209_create_comments_table.php
│   │   ├── 2024_08_30_123221_create_team_user_table.php
│   │   ├── 2024_08_30_125909_create_sessions_table.php
│   │   ├── 2024_08_31_074307_create_cache_table.php
│   │   └── 2024_09_04_215244_create_refresh_tokens_table.php
│   ├── seeders/
│   │   └── DatabaseSeeder.php
├── docker-compose.yml
├── nginx/
│   ├── default.conf
│   ├── php-fpm.conf
│   ├── php.ini
│   ├── snippets/
│   │   └── fastcgi-php.conf
├── package.json
├── phpunit.xml
├── public/
│   ├── .htaccess
│   ├── favicon.ico
│   ├── index.php
│   └── robots.txt
├── resources/
│   ├── css/
│   │   └── app.css
│   ├── js/
│   │   ├── app.js
│   │   └── bootstrap.js
│   ├── views/
│   │   └── welcome.blade.php
├── routes/
│   ├── api.php
│   └── console.php
├── storage/
│   ├── app/
│   │   ├── .gitignore
│   │   ├── public/
│   │   │   └── .gitignore
├── tests/
│   ├── Feature/
│   │   ├── AuthTest.php
│   │   ├── CommentTest.php
│   │   ├── TaskTest.php
│   │   └── TeamTest.php
│   ├── TestCase.php
│   ├── Unit/
│   │   ├── CommentTest.php
│   │   ├── TaskTest.php
│   │   ├── TeamTest.php
│   │   └── UserTest.php
└── vite.config.js
```

</details>


docker tag  0c45412e1e33b64f851762c8aac1b6251ca16b47f7da6aa8f49c894d4c2922ed  sni10per/tasks-php:v1.0.1

docker tag 478461549dd08b8aa16bb6052c29ee22d895cdc95a8c602d32ea609c1ea75352 sni10per/tasks-php:v1.0.1

docker push sni10per/tasks-php:v1.0.1

php artisan make:middleware RestAuthenticate

