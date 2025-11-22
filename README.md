# Laravel Task Management REST API

> **REST API на основе Laravel для совместного управления задачами и командами с аутентификацией через Sanctum**

[![Release](https://img.shields.io/github/v/release/sni10/Tasks-Laravel-REST-API-examples?style=for-the-badge&logo=github&logoColor=white)](https://github.com/sni10/Tasks-Laravel-REST-API-examples/releases)
[![Release Workflow](https://img.shields.io/github/actions/workflow/status/sni10/Tasks-Laravel-REST-API-examples/release.yml?style=for-the-badge&logo=githubactions&logoColor=white&label=Release)](https://github.com/sni10/Tasks-Laravel-REST-API-examples/actions/workflows/release.yml)
[![Tests](https://img.shields.io/github/actions/workflow/status/sni10/Tasks-Laravel-REST-API-examples/tests.yml?style=for-the-badge&logo=githubactions&logoColor=white&label=Tests)](https://github.com/sni10/Tasks-Laravel-REST-API-examples/actions/workflows/tests.yml)
[![Coverage](https://img.shields.io/badge/Coverage-65%25-brightgreen?style=for-the-badge&logo=codecov&logoColor=white)](https://github.com/sni10/Tasks-Laravel-REST-API-examples/actions/workflows/tests.yml)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?style=for-the-badge&logo=docker&logoColor=white)](https://www.docker.com/)
[![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)](LICENSE)

## Environments

Проект поддерживает два окружения, управляемых через Docker Compose:

### Production environment
- Использует `docker-compose.yml` как базовую конфигурацию
- Настроено для боевого развёртывания с оптимизированными параметрами
- Переменные окружения: `APP_ENV=prod`, `APP_DEBUG=false`
- База данных: `task_management`

### Development / Testing environment
- Использует `docker-compose.yml` + `docker/config-envs/test/docker-compose.override.yml`
- Включает отладку, покрытие кода и подробные сообщения об ошибках
- Переменные окружения: `APP_ENV=test`, `APP_DEBUG=true`
- База данных: `task_management_test`
- Xdebug включён для покрытия и отладки

## Running the Application

### Production
1. Клонируйте репозиторий:
```bash
git clone <repository-url>
cd tasks
```

2. Создайте файл `.env.prod` на основе `.env.example` с боевыми настройками

3. Соберите и запустите контейнеры:
```bash
docker compose build
docker compose up -d
```

4. API будет доступен по адресу `http://localhost:8000`

### Development / Testing
1. Создайте `.env.test` или `.env.dev` на основе `.env.example` с параметрами для test/dev

2. Соберите и поднимите окружение с тестовым override:
```bash
docker compose --env-file .env.test -f docker-compose.yml -f docker/config-envs/test/docker-compose.override.yml build

docker compose --env-file .env.test -f docker-compose.yml -f docker/config-envs/test/docker-compose.override.yml up -d
```

3. API будет доступен по адресу `http://localhost:8000`

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

## Authentication (Sanctum)
В дальнейшем предполагается расширение логики использования с распределением прав и
полномочий для выдаваемых токенов

---

### Example request `http://localhost:8000/api/v1/register`
<details>
  <summary>Показать пример</summary>

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

### Example request `http://localhost:8000/api/v1/login`
<details>
  <summary>Показать пример</summary>

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

### Example request `http://localhost:8000/api/v1/tasks`

<details>
  <summary>Показать пример</summary>

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

## Other examples of requests for group `api/v1`

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

Проект включает полноценное покрытие тестами на базе PHPUnit:
- **Unit-тесты** (`tests/Unit/`) — быстрые изолированные тесты для моделей и бизнес-логики (без БД)
- **Feature-тесты** (`tests/Feature/`) — интеграционные тесты для API-эндпоинтов с БД

### Running tests locally

Тесты запускаются внутри контейнера PHP, используя окружение разработки/тестирования.

1. Поднимите тестовое окружение:
```bash
docker compose --env-file .env.test -f docker-compose.yml -f docker/config-envs/test/docker-compose.override.yml up -d
```

2. Создайте тестовую базу данных:
```bash
docker compose --env-file .env.test exec mysql mysql -uroot -proot -e "CREATE DATABASE IF NOT EXISTS task_management_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

3. Примените миграции для тестовой базы данных:
```bash
docker compose --env-file .env.test exec php php artisan migrate:fresh --env=test
```

4. Запустите все тесты:
```bash
docker compose --env-file .env.test exec php vendor/bin/phpunit --colors=always --testdox
```

5. Запустите тесты с покрытием:
```bash
docker compose --env-file .env.test exec php vendor/bin/phpunit --coverage-text --colors=always --testdox
```

6. Сгенерируйте HTML-отчёт покрытия:
```bash
docker compose --env-file .env.test exec php vendor/bin/phpunit --coverage-html=storage/coverage-report
```

Откройте `storage/coverage-report/index.html` в браузере, чтобы посмотреть отчёт.

### Running individual tests

Запустить один файл тестов:
```bash
docker compose --env-file .env.test exec php vendor/bin/phpunit tests/Feature/TaskTest.php
```

Запустить конкретный тестовый метод:
```bash
docker compose --env-file .env.test exec php vendor/bin/phpunit --filter=testStoreTask
```

Запустить только Unit-тесты:
```bash
docker compose --env-file .env.test exec php vendor/bin/phpunit tests/Unit/
```

Запустить только Feature-тесты:
```bash
docker compose --env-file .env.test exec php vendor/bin/phpunit tests/Feature/
```

### CI/CD testing

Тесты автоматически запускаются в GitHub Actions при создании pull request в ветку `dev`. В пайплайне выполняется:
1. Сборка Docker-контейнеров с тестовой конфигурацией
2. Подготовка тестовой БД (`task_management_test`)
3. Применение миграций
4. Запуск всех тестов с покрытием
5. Загрузка отчётов покрытия как артефактов

Полную конфигурацию CI/CD смотрите в `.github/workflows/tests.yml`.

---
#### Sample test output

<details>
  <summary>Показать вывод</summary>

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


## File structure diagram

<details>
  <summary>Показать структуру</summary>

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

---

## Git Workflow and Releases

### Branching Strategy
- `main` — production-ready code
- `stage` — Staging (pre-production)
- `dev` — интеграционная ветка разработки

### Release Process
1. Фичи разрабатываются в feature-ветках и вливаются в `dev` через pull request
2. Тесты автоматически запускаются на каждый PR в `dev` (см. `.github/workflows/tests.yml`)
3. Когда `dev` стабилен, создайте PR из `dev` → `stage`
4. После валидации `stage` создайте PR из `stage` → `main`
5. После мержа PR `stage` → `main` автоматически создаётся новый релиз с инкрементом версии (см. `.github/workflows/release.yml`)

### Automated Release Creation
- Триггерится при мерже PR из `stage` в `main`
- Автоматически увеличивается patch-версия (например, v1.0.0 → v1.0.1)
- Создаётся релиз GitHub с changelog
- Теги в формате семантического версионирования: `vMAJOR.MINOR.PATCH`

---

## License
MIT License — see [LICENSE](LICENSE) for details
