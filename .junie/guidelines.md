# Руководство по разработке и тестированию (Laravel Tasks API)

Этот документ фиксирует обязательные правила и практики для текущего репозитория. Он содержит: инструкции по сборке/конфигурации, сведения о тестировании (включая демонстрационный тест) и рекомендации по разработке/отладке. Предназначен для опытных разработчиков.

1) Обзор
- Стек: PHP 8.2, Laravel 11, Sanctum, MySQL 8, Redis 7, Nginx.
- Контейнеризация: docker compose (сервисы nginx, php, mysql, redis).
- Покрытие тестов: PHPUnit + Xdebug (HTML отчёт: storage\coverage-report).
- Структура слоёв: Controllers → Services → Repositories → Eloquent Models.

2) Сборка и конфигурация (Windows + Docker)
- Сборка и запуск: "docker compose build php" затем "docker compose up -d".
- Установка зависимостей внутри контейнера php: "docker compose exec php bash -lc \"composer install --no-interaction --prefer-dist --optimize-autoloader\"".
- Генерация ключа приложения: "docker compose exec php php artisan key:generate".
- База данных по умолчанию: DB_HOST=mysql, DB_DATABASE=task_management, DB_USERNAME=root, DB_PASSWORD=root (см. docker-compose.yml). Проверьте .env при необходимости.
- Миграции для dev: "docker compose exec php php artisan migrate --force". Сиды опционально: "docker compose exec php php artisan db:seed".

3) Тестирование
3.1 Минимальный прогон (без БД) — демонстрационный тест
- Создайте временный файл tests\\Unit\\SanityTest.php со следующим содержимым (без Laravel bootstrap):
  namespace Tests\\Unit; use PHPUnit\\Framework\\TestCase; class SanityTest extends TestCase { public function test_truthy(): void { $this->assertTrue(true); } }
- Запустите только этот тест: "docker compose exec php vendor/bin/phpunit --colors=always --testdox --filter=SanityTest".
- Удалите файл tests\\Unit\\SanityTest.php после демонстрации (не оставляйте временные тесты в репозитории).

3.2 Полный прогон (с БД и покрытием)
- Подготовьте тестовую БД (отдельно от dev):
  1) Создайте БД: "docker compose exec mysql mysql -uroot -proot -e \"CREATE DATABASE IF NOT EXISTS task_management_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\"".
  2) Скопируйте .env в .env.test и задайте: DB_HOST=mysql; DB_DATABASE=task_management_test; DB_USERNAME=root; DB_PASSWORD=root; QUEUE_CONNECTION=sync; CACHE_STORE=array.
  3) Примените миграции: "docker compose exec php bash -lc \"php artisan migrate:fresh --env=test\"".
- Запуск тестов:
  - Все: "docker compose exec php vendor/bin/phpunit --colors=always".
  - Подробно: "docker compose exec php vendor/bin/phpunit -v".
  - Один файл: "docker compose exec php vendor/bin/phpunit tests/Feature/TaskTest.php -v".
  - Фильтр по методу: "docker compose exec php vendor/bin/phpunit --filter=Tests\\Feature\\TaskTest::testShowTask -v".
- Покрытие:
  - Текст: "docker compose exec php vendor/bin/phpunit --coverage-text --colors=always --testdox".
  - HTML: см. storage\\coverage-report (phpunit.xml уже настроен).

Примечания по тестам
- Большинство текущих тестов расширяют Tests\\TestCase, который выполняет очистку таблиц MySQL через SET FOREIGN_KEY_CHECKS — используйте MySQL (а не SQLite-in-memory).
- Перед прогонами полезно очищать кэши: "docker compose exec php php artisan optimize:clear".

4) Инженерные правила
- Стиль: PSR-12. Форматирование: Laravel Pint (запуск: "docker compose exec php vendor/bin/pint").
- Controllers: без SQL и сложных ветвлений; валидация через Form Request; ответы — только через HTTP-контракты.
- Services: бизнес-логика, транзакции (DB::transaction), логирование важных событий и ошибок.
- Repositories: вся работа с Eloquent/SQL здесь; никаких сетевых вызовов.
- Models: только данные/связи/простые скоупы; без бизнес-логики.
- DI: зависимости через интерфейсы (Contracts) и контейнер Laravel; без статических синглтонов.
- Ошибки: не подавлять исключения без логирования; добавляйте контекст в логи.
- Тесты обязательны для всех изменений логики; PR без тестов отклонять.

5) Отладка и частые проблемы
- БД: если видите ошибки FOREIGN_KEY или SQLSTATE, проверьте, что mysql контейнер запущен, миграции применены в среде test (см. .env.test).
- Покрытие: для покрытия требуется Xdebug (в контейнере он установлен). Запускайте phpunit с флагами покрытия.
- Конфиги/кэши: после изменений в .env или провайдерах выполните "docker compose exec php php artisan optimize:clear".
- Окружение тестов: phpunit.xml задаёт APP_ENV=test; для artisan используйте флаг --env=test при выполнении тестовых миграций.

6) Шпаргалка команд (PowerShell)
- Поднять окружение: docker compose up -d
- Обновить зависимости: docker compose exec php composer install
- Ключ приложения: docker compose exec php php artisan key:generate
- Миграции dev: docker compose exec php php artisan migrate --force
- Миграции test: docker compose exec php bash -lc "php artisan migrate:fresh --env=test"
- Тесты: docker compose exec php vendor/bin/phpunit --colors=always
- Тесты с покрытием: docker compose exec php vendor/bin/phpunit --coverage-text --colors=always --testdox
- Очистка кэшей: docker compose exec php php artisan optimize:clear

7) Чистота репозитория
- Не коммитьте временные тесты (например, SanityTest) и артефакты локальных проверок. Для демонстрации процесса создавайте такие файлы локально и удаляйте после запуска. В репозитории остаётся только этот документ (.junie/guidelines.md).

8) API и эндпоинты (REST, Sanctum)
- Базовый URL: http://localhost:8000/api/v1
- Формат: JSON только. Заголовки: "Accept: application/json", "Content-Type: application/json".
- Аутентификация: Bearer-токен (Laravel Sanctum). Получаете токен через /login или обновляете через /refresh. Передавайте: "Authorization: Bearer {TOKEN}".
- Версионирование: все маршруты находятся под префиксом v1; новые несовместимые изменения добавляйте как v2 и т.д.
- Пагинация: стандартная Laravel пагинация по параметру page (начиная с 1). Доп. параметры (per_page и фильтры) зависят от конкретных реализаций контроллеров; по умолчанию можно опираться на page.
- Ошибки: 401 (Unauthorized) при отсутствии/невалидном токене; 403 (Forbidden) при отсутствии прав; 404 (Not Found); 422 (Validation Error) с полем errors; 500 (Server Error). Все ответы в JSON.

8.1 Общий формат ошибок
- 401: { "message": "Unauthenticated." }
- 422: { "message": "The given data was invalid.", "errors": { "field": ["..." ] } }

8.2 Аутентификация
- POST /api/v1/register
  - Тело: { "name": "string", "email": "string", "password": "string", "password_confirmation": "string" }
  - Ответ 201/200: { "user": { ... }, "token": "<plain_text_token>" }
  - Пример (PowerShell curl):
    curl -X POST http://localhost:8000/api/v1/register -H "Accept: application/json" -H "Content-Type: application/json" -d '{"name":"John","email":"john@example.com","password":"secret123","password_confirmation":"secret123"}'

- POST /api/v1/login
  - Тело: { "email": "string", "password": "string" }
  - Ответ 200: { "user": { ... }, "token": "<plain_text_token>" }
  - Пример: curl -X POST http://localhost:8000/api/v1/login -H "Accept: application/json" -H "Content-Type: application/json" -d '{"email":"john@example.com","password":"secret123"}'

- POST /api/v1/refresh
  - Требует заголовок Authorization: Bearer {TOKEN}
  - Назначение: выдать новый токен (конкретная политика жизни токена определяется сервисом аутентификации проекта).
  - Ответ 200: { "token": "<plain_text_token>" }

- POST /api/v1/logout
  - Требует Bearer-токен. Инвалидация текущего токена.
  - Ответ 204/200: { "message": "Logged out" } или пустое тело.

8.3 Задачи (Tasks)
- GET /api/v1/tasks (auth)
  - Параметры: page (int, опционально)
  - Ответ 200: { "data": [ { "id": 1, "title": "...", "description": "...", "status": "pending", "user_id": <int>, ... } ], "links": { ... }, "meta": { ... } }
  - Пример: curl -H "Authorization: Bearer {TOKEN}" -H "Accept: application/json" http://localhost:8000/api/v1/tasks

- POST /api/v1/tasks (auth)
  - Тело: { "title": "string" (required), "description": "string" (optional), "status": "string" (optional; по умолчанию 'pending') }
  - Ответ 201/200: объект задачи

- GET /api/v1/tasks/{id} (auth)
  - Ответ 200: объект задачи; 404 если нет

- PUT /api/v1/tasks/{id} (auth)
  - Тело: любые изменяемые поля задачи: { "title"?: "string", "description"?: "string", "status"?: "string" }
  - Ответ 200: обновлённый объект

- DELETE /api/v1/tasks/{id} (auth)
  - Ответ 204/200

8.4 Комментарии (Comments)
- GET /api/v1/tasks/{taskId}/comments (auth)
  - Ответ 200: [ { "id": <int>, "content": "string", "task_id": <int>, "user_id": <int>, ... } ]

- POST /api/v1/tasks/{taskId}/comments (auth)
  - Тело: { "content": "string" (required) }
  - Ответ 201/200: объект комментария

- DELETE /api/v1/comments/{id} (auth)
  - Ответ 204/200

8.5 Команды (Teams)
- GET /api/v1/teams (auth)
  - Ответ 200: список команд (с пагинацией при необходимости)

- POST /api/v1/teams (auth)
  - Тело: { "name": "string" (required) }
  - Ответ 201/200: объект команды

- GET /api/v1/teams/{id} (auth)
  - Ответ 200: объект команды

- PUT /api/v1/teams/{id} (auth)
  - Тело: { "name"?: "string" }
  - Ответ 200: обновлённый объект

- DELETE /api/v1/teams/{id} (auth)
  - Ответ 204/200

- POST /api/v1/teams/{team}/users (auth)
  - Тело: { "user_id": <int> }
  - Добавляет пользователя в команду (создаёт запись в pivot-таблице team_user).
  - Ответ 200/201

- DELETE /api/v1/teams/{team}/users/{user} (auth)
  - Удаляет пользователя из команды.
  - Ответ 204/200

8.6 Быстрый старт (через curl)
- 1) Регистрация (или используйте существующего пользователя):
  $resp = curl -s -X POST http://localhost:8000/api/v1/login -H "Accept: application/json" -H "Content-Type: application/json" -d '{"email":"test@test.com","password":"password123"}'
  Извлеките token из ответа и подставьте в следующие вызовы.

- 2) Создать задачу:
  curl -X POST http://localhost:8000/api/v1/tasks -H "Authorization: Bearer {TOKEN}" -H "Accept: application/json" -H "Content-Type: application/json" -d '{"title":"Initial Task","description":"Some description"}'

- 3) Добавить комментарий к задаче:
  curl -X POST http://localhost:8000/api/v1/tasks/{TASK_ID}/comments -H "Authorization: Bearer {TOKEN}" -H "Accept: application/json" -H "Content-Type: application/json" -d '{"content":"My comment"}'

- 4) Добавить пользователя в команду:
  curl -X POST http://localhost:8000/api/v1/teams/{TEAM_ID}/users -H "Authorization: Bearer {TOKEN}" -H "Accept: application/json" -H "Content-Type: application/json" -d '{"user_id": 1}'

Примечание: все тела и ответы приведены в обобщённом виде и соответствуют маршрутам из routes\api.php. Для расширений (фильтры, сортировка, дополнительные поля) придерживайтесь разделения по слоям: контроллер (валидация), сервис (бизнес-логика), репозиторий (доступ к данным).
