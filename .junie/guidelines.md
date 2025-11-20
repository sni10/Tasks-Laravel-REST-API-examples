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
  2) Скопируйте .env в .env.testing и задайте: DB_HOST=mysql; DB_DATABASE=task_management_test; DB_USERNAME=root; DB_PASSWORD=root; QUEUE_CONNECTION=sync; CACHE_STORE=array.
  3) Примените миграции: "docker compose exec php bash -lc \"php artisan migrate:fresh --env=testing\"".
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
- БД: если видите ошибки FOREIGN_KEY или SQLSTATE, проверьте, что mysql контейнер запущен, миграции применены в среде testing (см. .env.testing).
- Покрытие: для покрытия требуется Xdebug (в контейнере он установлен). Запускайте phpunit с флагами покрытия.
- Конфиги/кэши: после изменений в .env или провайдерах выполните "docker compose exec php php artisan optimize:clear".
- Окружение тестов: phpunit.xml задаёт APP_ENV=testing; для artisan используйте флаг --env=testing при выполнении тестовых миграций.

6) Шпаргалка команд (PowerShell)
- Поднять окружение: docker compose up -d
- Обновить зависимости: docker compose exec php composer install
- Ключ приложения: docker compose exec php php artisan key:generate
- Миграции dev: docker compose exec php php artisan migrate --force
- Миграции testing: docker compose exec php bash -lc "php artisan migrate:fresh --env=testing"
- Тесты: docker compose exec php vendor/bin/phpunit --colors=always
- Тесты с покрытием: docker compose exec php vendor/bin/phpunit --coverage-text --colors=always --testdox
- Очистка кэшей: docker compose exec php php artisan optimize:clear

7) Чистота репозитория
- Не коммитьте временные тесты (например, SanityTest) и артефакты локальных проверок. Для демонстрации процесса создавайте такие файлы локально и удаляйте после запуска. В репозитории остаётся только этот документ (.junie/guidelines.md).
