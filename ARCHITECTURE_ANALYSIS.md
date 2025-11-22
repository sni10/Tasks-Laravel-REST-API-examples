# Анализ архитектуры и план миграции на CQRS + RabbitMQ

## Содержание
1. [Текущая архитектура](#1-текущая-архитектура)
2. [Применяемые принципы и паттерны](#2-применяемые-принципы-и-паттерны)
3. [Диаграмма текущей архитектуры](#3-диаграмма-текущей-архитектуры)
4. [Что такое CQRS](#4-что-такое-cqrs)
5. [План миграции на CQRS + RabbitMQ](#5-план-миграции-на-cqrs--rabbitmq)
6. [Оценка трудозатрат](#6-оценка-трудозатрат)

---

## 1. Текущая архитектура

### 1.1 Тип архитектуры: **Layered Architecture (N-Tier)**

Проект реализует классическую **трёхслойную архитектуру** с чётким разделением ответственности:

| Слой | Компоненты | Ответственность |
|------|-----------|-----------------|
| **Presentation** | Controllers (Api/) | HTTP запросы/ответы, валидация входных данных |
| **Business Logic** | Services | Бизнес-правила, оркестрация операций |
| **Data Access** | Repositories | CRUD операции, работа с БД через Eloquent |
| **Domain** | Models | Сущности данных, связи между ними |

### 1.2 Структура проекта

```
app/
├── Contracts/                    # Интерфейсы (10 файлов)
│   ├── *RepositoryInterface.php  # Контракты репозиториев
│   └── *ServiceInterface.php     # Контракты сервисов
├── Http/Controllers/Api/         # REST контроллеры
│   ├── AuthController.php
│   ├── TaskController.php
│   ├── CommentController.php
│   └── TeamController.php
├── Services/                     # Бизнес-логика
│   ├── UserService.php
│   ├── TaskService.php
│   ├── CommentService.php
│   ├── TeamService.php
│   └── TokenService.php
├── Repositories/                 # Доступ к данным
│   └── Eloquent*Repository.php   # 5 реализаций
├── Models/                       # Eloquent модели
│   ├── User.php
│   ├── Task.php
│   ├── Team.php
│   ├── Comment.php
│   └── RefreshToken.php
└── Providers/
    └── AppServiceProvider.php    # DI биндинги
```

### 1.3 Поток данных (текущий)

```
HTTP Request
     ↓
Controller (валидация)
     ↓
Service (бизнес-логика)
     ↓
Repository (доступ к данным)
     ↓
Model (Eloquent ORM)
     ↓
Database (MySQL)
     ↓
Response ← тот же путь обратно
```

**Проблема:** Чтение и запись используют одни и те же модели, сервисы и репозитории. Нет разделения ответственности между операциями чтения и записи.

---

## 2. Применяемые принципы и паттерны

### 2.1 Паттерны проектирования

| Паттерн | Где применяется | Описание |
|---------|----------------|----------|
| **Repository** | `app/Repositories/` | Абстрагирует доступ к данным от бизнес-логики |
| **Service Layer** | `app/Services/` | Инкапсулирует бизнес-правила |
| **Dependency Injection** | `AppServiceProvider` | Инверсия управления зависимостями |
| **Interface Segregation** | `app/Contracts/` | Отдельные интерфейсы для каждой сущности |
| **Active Record** | `app/Models/` | Eloquent ORM модели |

### 2.2 Принципы SOLID

| Принцип | Соблюдение | Комментарий |
|---------|-----------|-------------|
| **S** - Single Responsibility | ✅ Да | Каждый класс имеет одну ответственность |
| **O** - Open/Closed | ✅ Частично | Можно расширять через интерфейсы |
| **L** - Liskov Substitution | ✅ Да | Реализации заменяемы через интерфейсы |
| **I** - Interface Segregation | ✅ Да | 10 отдельных интерфейсов |
| **D** - Dependency Inversion | ✅ Да | Зависимости через абстракции |

### 2.3 Что отсутствует в текущей архитектуре

- ❌ **Events/Listeners** — нет событийной модели
- ❌ **Jobs/Queues** — нет асинхронной обработки
- ❌ **CQRS** — чтение и запись не разделены
- ❌ **Event Sourcing** — состояние не хранится как события
- ❌ **Message Broker** — нет очередей сообщений

---

## 3. Диаграмма текущей архитектуры

```
┌─────────────────────────────────────────────────────────────┐
│                      HTTP Layer                              │
│  ┌─────────────────────────────────────────────────────┐    │
│  │              REST API Controllers                    │    │
│  │   AuthController | TaskController | TeamController   │    │
│  └────────────────────────┬────────────────────────────┘    │
└───────────────────────────┼─────────────────────────────────┘
                            │ DI (Interfaces)
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                    Service Layer                             │
│  ┌─────────────────────────────────────────────────────┐    │
│  │    UserService | TaskService | TeamService | etc.    │    │
│  │         implements *ServiceInterface                 │    │
│  └────────────────────────┬────────────────────────────┘    │
└───────────────────────────┼─────────────────────────────────┘
                            │ DI (Interfaces)
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                   Repository Layer                           │
│  ┌─────────────────────────────────────────────────────┐    │
│  │  EloquentUserRepository | EloquentTaskRepository     │    │
│  │         implements *RepositoryInterface              │    │
│  └────────────────────────┬────────────────────────────┘    │
└───────────────────────────┼─────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                     Data Layer                               │
│  ┌──────────────────┐    ┌────────────────────────────┐     │
│  │  Eloquent Models │───►│     MySQL Database          │     │
│  │  (Active Record) │    │  (Single Read/Write DB)     │     │
│  └──────────────────┘    └────────────────────────────┘     │
└─────────────────────────────────────────────────────────────┘
```

---

## 4. Что такое CQRS

### 4.1 Определение

**CQRS (Command Query Responsibility Segregation)** — архитектурный паттерн, разделяющий операции чтения (Query) и записи (Command) на отдельные модели.

### 4.2 Ключевые концепции

```
┌─────────────────────────────────────────────────────────────┐
│                         CQRS                                 │
├─────────────────────────┬───────────────────────────────────┤
│      COMMAND Side       │           QUERY Side              │
├─────────────────────────┼───────────────────────────────────┤
│  - Изменяет состояние   │  - Только чтение                  │
│  - Валидация бизнес-    │  - Оптимизировано для отображения │
│    правил               │  - Может использовать             │
│  - Генерирует события   │    денормализованные данные       │
│  - Асинхронная          │  - Синхронная обработка           │
│    обработка            │  - Read Models / Projections      │
└─────────────────────────┴───────────────────────────────────┘
```

### 4.3 Преимущества CQRS

1. **Масштабируемость** — чтение и запись масштабируются независимо
2. **Производительность** — оптимизация моделей под конкретные задачи
3. **Гибкость** — разные хранилища для чтения/записи
4. **Отказоустойчивость** — события можно переиграть
5. **Аудит** — полная история изменений

### 4.4 Когда использовать CQRS

✅ **Подходит:**
- Высокая нагрузка на чтение/запись
- Сложная бизнес-логика
- Требуется аудит изменений
- Микросервисная архитектура

❌ **Не подходит:**
- Простые CRUD приложения
- Маленькие команды
- Жёсткие сроки MVP

---

## 5. План миграции на CQRS + RabbitMQ

### 5.1 Целевая архитектура

```
┌─────────────────────────────────────────────────────────────────────────┐
│                           HTTP Layer                                     │
│  ┌────────────────────────────┐    ┌────────────────────────────┐       │
│  │    Command Controllers     │    │     Query Controllers      │       │
│  │  (POST, PUT, DELETE)       │    │        (GET)                │       │
│  └─────────────┬──────────────┘    └─────────────┬──────────────┘       │
└────────────────┼────────────────────────────────┼───────────────────────┘
                 │                                 │
                 ▼                                 ▼
┌────────────────────────────────┐  ┌────────────────────────────────────┐
│        COMMAND SIDE            │  │           QUERY SIDE               │
│  ┌──────────────────────────┐  │  │  ┌──────────────────────────────┐  │
│  │     Command Bus          │  │  │  │       Query Service          │  │
│  │  (Dispatch Commands)     │  │  │  │   (Read Optimized)           │  │
│  └────────────┬─────────────┘  │  │  └─────────────┬────────────────┘  │
│               │                │  │                │                   │
│               ▼                │  │                ▼                   │
│  ┌──────────────────────────┐  │  │  ┌──────────────────────────────┐  │
│  │    Command Handlers      │  │  │  │      Read Repositories       │  │
│  │  - CreateTaskHandler     │  │  │  │   (Denormalized Views)       │  │
│  │  - UpdateTaskHandler     │  │  │  └─────────────┬────────────────┘  │
│  │  - DeleteTaskHandler     │  │  │                │                   │
│  └────────────┬─────────────┘  │  │                ▼                   │
│               │                │  │  ┌──────────────────────────────┐  │
│               ▼                │  │  │      Read Database           │  │
│  ┌──────────────────────────┐  │  │  │   (MySQL Replica / Redis)    │  │
│  │   Write Repository       │  │  │  └──────────────────────────────┘  │
│  │   (Aggregate Root)       │  │  └────────────────────────────────────┘
│  └────────────┬─────────────┘  │
│               │                │
│               ▼                │
│  ┌──────────────────────────┐  │
│  │    Event Dispatcher      │──┼──────────────────┐
│  │  - TaskCreatedEvent      │  │                  │
│  │  - TaskUpdatedEvent      │  │                  │
│  └────────────┬─────────────┘  │                  │
└───────────────┼────────────────┘                  │
                │                                   │
                ▼                                   ▼
┌───────────────────────────────────────────────────────────────────────┐
│                          RabbitMQ                                      │
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────────┐    │
│  │  task.created   │  │  task.updated   │  │   task.deleted      │    │
│  │    (queue)      │  │    (queue)      │  │     (queue)         │    │
│  └────────┬────────┘  └────────┬────────┘  └──────────┬──────────┘    │
└───────────┼────────────────────┼─────────────────────┼────────────────┘
            │                    │                     │
            ▼                    ▼                     ▼
┌───────────────────────────────────────────────────────────────────────┐
│                        Event Handlers (Workers)                        │
│  ┌─────────────────────────────────────────────────────────────────┐  │
│  │  - UpdateReadModelHandler (синхронизация Read DB)               │  │
│  │  - NotificationHandler (отправка уведомлений)                   │  │
│  │  - AuditLogHandler (запись в лог аудита)                        │  │
│  │  - SearchIndexHandler (обновление Elasticsearch)                │  │
│  └─────────────────────────────────────────────────────────────────┘  │
└───────────────────────────────────────────────────────────────────────┘
```

### 5.2 Новая структура директорий

```
app/
├── Commands/                      # Command объекты
│   ├── Task/
│   │   ├── CreateTaskCommand.php
│   │   ├── UpdateTaskCommand.php
│   │   └── DeleteTaskCommand.php
│   ├── Comment/
│   └── Team/
│
├── CommandHandlers/               # Обработчики команд
│   ├── Task/
│   │   ├── CreateTaskHandler.php
│   │   ├── UpdateTaskHandler.php
│   │   └── DeleteTaskHandler.php
│   ├── Comment/
│   └── Team/
│
├── Queries/                       # Query объекты
│   ├── Task/
│   │   ├── GetAllTasksQuery.php
│   │   ├── GetTaskByIdQuery.php
│   │   └── GetTasksByUserQuery.php
│   ├── Comment/
│   └── Team/
│
├── QueryHandlers/                 # Обработчики запросов
│   ├── Task/
│   │   ├── GetAllTasksHandler.php
│   │   ├── GetTaskByIdHandler.php
│   │   └── GetTasksByUserHandler.php
│   ├── Comment/
│   └── Team/
│
├── Events/                        # Domain Events
│   ├── Task/
│   │   ├── TaskCreatedEvent.php
│   │   ├── TaskUpdatedEvent.php
│   │   └── TaskDeletedEvent.php
│   ├── Comment/
│   └── Team/
│
├── EventHandlers/                 # Обработчики событий (Listeners)
│   ├── Task/
│   │   ├── UpdateTaskReadModelHandler.php
│   │   ├── SendTaskNotificationHandler.php
│   │   └── LogTaskAuditHandler.php
│   ├── Comment/
│   └── Team/
│
├── ReadModels/                    # Модели для чтения (денормализованные)
│   ├── TaskReadModel.php
│   ├── TaskListReadModel.php
│   └── TaskDetailReadModel.php
│
├── WriteModels/                   # Модели для записи (Aggregates)
│   ├── TaskAggregate.php
│   └── TeamAggregate.php
│
├── Bus/                           # Command/Query Bus
│   ├── CommandBus.php
│   ├── CommandBusInterface.php
│   ├── QueryBus.php
│   └── QueryBusInterface.php
│
├── Repositories/
│   ├── Write/                     # Репозитории для записи
│   │   ├── TaskWriteRepository.php
│   │   └── TaskWriteRepositoryInterface.php
│   └── Read/                      # Репозитории для чтения
│       ├── TaskReadRepository.php
│       └── TaskReadRepositoryInterface.php
│
└── Jobs/                          # Queue Jobs для RabbitMQ
    ├── ProcessTaskCreatedJob.php
    ├── ProcessTaskUpdatedJob.php
    └── SyncReadModelJob.php
```

### 5.3 Шаги миграции

#### Этап 1: Подготовка инфраструктуры

**1.1 Установка зависимостей**

```bash
# RabbitMQ драйвер для Laravel
composer require vladimir-yuldashev/laravel-queue-rabbitmq

# CQRS библиотека (опционально)
composer require spatie/laravel-event-sourcing
# или
composer require broadway/broadway
```

**1.2 Добавление RabbitMQ в docker-compose.yml**

```yaml
services:
  # ... existing services ...

  rabbitmq:
    image: rabbitmq:3.12-management-alpine
    container_name: tasks_rabbitmq
    ports:
      - "5672:5672"    # AMQP
      - "15672:15672"  # Management UI
    environment:
      RABBITMQ_DEFAULT_USER: guest
      RABBITMQ_DEFAULT_PASS: guest
    volumes:
      - rabbitmq_data:/var/lib/rabbitmq
    healthcheck:
      test: rabbitmq-diagnostics -q ping
      interval: 30s
      timeout: 10s
      retries: 5

volumes:
  rabbitmq_data:
```

**1.3 Конфигурация .env**

```env
# RabbitMQ
RABBITMQ_HOST=rabbitmq
RABBITMQ_PORT=5672
RABBITMQ_USER=guest
RABBITMQ_PASSWORD=guest
RABBITMQ_VHOST=/

# Queue
QUEUE_CONNECTION=rabbitmq
```

**1.4 Конфигурация config/queue.php**

```php
'connections' => [
    'rabbitmq' => [
        'driver' => 'rabbitmq',
        'queue' => env('RABBITMQ_QUEUE', 'default'),
        'connection' => PhpAmqpLib\Connection\AMQPLazyConnection::class,
        'hosts' => [
            [
                'host' => env('RABBITMQ_HOST', 'rabbitmq'),
                'port' => env('RABBITMQ_PORT', 5672),
                'user' => env('RABBITMQ_USER', 'guest'),
                'password' => env('RABBITMQ_PASSWORD', 'guest'),
                'vhost' => env('RABBITMQ_VHOST', '/'),
            ],
        ],
        'options' => [
            'ssl_options' => [
                'cafile' => env('RABBITMQ_SSL_CAFILE'),
                'local_cert' => env('RABBITMQ_SSL_LOCALCERT'),
                'local_key' => env('RABBITMQ_SSL_LOCALKEY'),
                'verify_peer' => env('RABBITMQ_SSL_VERIFY_PEER', true),
                'passphrase' => env('RABBITMQ_SSL_PASSPHRASE'),
            ],
            'queue' => [
                'job' => \VladimirYuldashev\LaravelQueueRabbitMQ\Queue\Jobs\RabbitMQJob::class,
            ],
        ],
    ],
],
```

#### Этап 2: Создание базовых классов CQRS

**2.1 Command Base Class**

```php
// app/Commands/Command.php
namespace App\Commands;

abstract class Command
{
    public readonly string $commandId;
    public readonly \DateTimeImmutable $occurredAt;

    public function __construct()
    {
        $this->commandId = (string) \Illuminate\Support\Str::uuid();
        $this->occurredAt = new \DateTimeImmutable();
    }
}
```

**2.2 Query Base Class**

```php
// app/Queries/Query.php
namespace App\Queries;

abstract class Query
{
    public readonly string $queryId;

    public function __construct()
    {
        $this->queryId = (string) \Illuminate\Support\Str::uuid();
    }
}
```

**2.3 Domain Event Base Class**

```php
// app/Events/DomainEvent.php
namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class DomainEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public readonly string $eventId;
    public readonly string $aggregateId;
    public readonly \DateTimeImmutable $occurredAt;
    public readonly int $version;

    public function __construct(string $aggregateId, int $version = 1)
    {
        $this->eventId = (string) \Illuminate\Support\Str::uuid();
        $this->aggregateId = $aggregateId;
        $this->occurredAt = new \DateTimeImmutable();
        $this->version = $version;
    }
}
```

**2.4 Command Bus Interface**

```php
// app/Bus/CommandBusInterface.php
namespace App\Bus;

use App\Commands\Command;

interface CommandBusInterface
{
    public function dispatch(Command $command): mixed;
}
```

**2.5 Command Bus Implementation**

```php
// app/Bus/CommandBus.php
namespace App\Bus;

use App\Commands\Command;
use Illuminate\Contracts\Container\Container;

class CommandBus implements CommandBusInterface
{
    public function __construct(
        private readonly Container $container
    ) {}

    public function dispatch(Command $command): mixed
    {
        $handlerClass = $this->resolveHandler($command);
        $handler = $this->container->make($handlerClass);

        return $handler->handle($command);
    }

    private function resolveHandler(Command $command): string
    {
        $commandClass = get_class($command);
        $handlerClass = str_replace('Commands', 'CommandHandlers', $commandClass);
        $handlerClass = str_replace('Command', 'Handler', $handlerClass);

        return $handlerClass;
    }
}
```

#### Этап 3: Рефакторинг Task (пример)

**3.1 CreateTaskCommand**

```php
// app/Commands/Task/CreateTaskCommand.php
namespace App\Commands\Task;

use App\Commands\Command;

final class CreateTaskCommand extends Command
{
    public function __construct(
        public readonly string $title,
        public readonly ?string $description,
        public readonly string $status,
        public readonly int $userId,
        public readonly ?int $teamId = null,
    ) {
        parent::__construct();
    }
}
```

**3.2 CreateTaskHandler**

```php
// app/CommandHandlers/Task/CreateTaskHandler.php
namespace App\CommandHandlers\Task;

use App\Commands\Task\CreateTaskCommand;
use App\Events\Task\TaskCreatedEvent;
use App\Repositories\Write\TaskWriteRepositoryInterface;

final class CreateTaskHandler
{
    public function __construct(
        private readonly TaskWriteRepositoryInterface $repository
    ) {}

    public function handle(CreateTaskCommand $command): int
    {
        $task = $this->repository->create([
            'title' => $command->title,
            'description' => $command->description,
            'status' => $command->status,
            'user_id' => $command->userId,
            'team_id' => $command->teamId,
        ]);

        // Dispatch domain event
        TaskCreatedEvent::dispatch(
            aggregateId: (string) $task->id,
            title: $task->title,
            description: $task->description,
            status: $task->status,
            userId: $task->user_id,
            teamId: $task->team_id,
        );

        return $task->id;
    }
}
```

**3.3 TaskCreatedEvent**

```php
// app/Events/Task/TaskCreatedEvent.php
namespace App\Events\Task;

use App\Events\DomainEvent;

final class TaskCreatedEvent extends DomainEvent
{
    public function __construct(
        string $aggregateId,
        public readonly string $title,
        public readonly ?string $description,
        public readonly string $status,
        public readonly int $userId,
        public readonly ?int $teamId,
    ) {
        parent::__construct($aggregateId);
    }

    public function broadcastOn(): array
    {
        return ['tasks'];
    }
}
```

**3.4 Event Handler (Listener)**

```php
// app/EventHandlers/Task/UpdateTaskReadModelHandler.php
namespace App\EventHandlers\Task;

use App\Events\Task\TaskCreatedEvent;
use App\ReadModels\TaskReadModel;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateTaskReadModelHandler implements ShouldQueue
{
    public string $connection = 'rabbitmq';
    public string $queue = 'read-model-sync';

    public function handle(TaskCreatedEvent $event): void
    {
        TaskReadModel::create([
            'task_id' => $event->aggregateId,
            'title' => $event->title,
            'description' => $event->description,
            'status' => $event->status,
            'user_id' => $event->userId,
            'team_id' => $event->teamId,
            'created_at' => $event->occurredAt,
        ]);
    }
}
```

**3.5 GetAllTasksQuery**

```php
// app/Queries/Task/GetAllTasksQuery.php
namespace App\Queries\Task;

use App\Queries\Query;

final class GetAllTasksQuery extends Query
{
    public function __construct(
        public readonly ?int $userId = null,
        public readonly ?int $teamId = null,
        public readonly ?string $status = null,
        public readonly int $page = 1,
        public readonly int $perPage = 15,
    ) {
        parent::__construct();
    }
}
```

**3.6 GetAllTasksHandler**

```php
// app/QueryHandlers/Task/GetAllTasksHandler.php
namespace App\QueryHandlers\Task;

use App\Queries\Task\GetAllTasksQuery;
use App\Repositories\Read\TaskReadRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

final class GetAllTasksHandler
{
    public function __construct(
        private readonly TaskReadRepositoryInterface $repository
    ) {}

    public function handle(GetAllTasksQuery $query): LengthAwarePaginator
    {
        return $this->repository->paginate(
            filters: [
                'user_id' => $query->userId,
                'team_id' => $query->teamId,
                'status' => $query->status,
            ],
            page: $query->page,
            perPage: $query->perPage,
        );
    }
}
```

**3.7 Новый TaskController**

```php
// app/Http/Controllers/Api/TaskController.php
namespace App\Http\Controllers\Api;

use App\Bus\CommandBusInterface;
use App\Bus\QueryBusInterface;
use App\Commands\Task\CreateTaskCommand;
use App\Commands\Task\UpdateTaskCommand;
use App\Commands\Task\DeleteTaskCommand;
use App\Queries\Task\GetAllTasksQuery;
use App\Queries\Task\GetTaskByIdQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(
        private readonly CommandBusInterface $commandBus,
        private readonly QueryBusInterface $queryBus,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = new GetAllTasksQuery(
            userId: $request->input('user_id'),
            teamId: $request->input('team_id'),
            status: $request->input('status'),
            page: $request->input('page', 1),
        );

        $tasks = $this->queryBus->dispatch($query);

        return response()->json($tasks);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|string|in:pending,in progress,completed',
            'team_id' => 'nullable|exists:teams,id',
        ]);

        $command = new CreateTaskCommand(
            title: $validated['title'],
            description: $validated['description'] ?? null,
            status: $validated['status'] ?? 'pending',
            userId: $request->user()->id,
            teamId: $validated['team_id'] ?? null,
        );

        $taskId = $this->commandBus->dispatch($command);

        return response()->json(['id' => $taskId], 201);
    }

    public function show(int $id): JsonResponse
    {
        $query = new GetTaskByIdQuery($id);
        $task = $this->queryBus->dispatch($query);

        if (!$task) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        return response()->json($task);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|string|in:pending,in progress,completed',
        ]);

        $command = new UpdateTaskCommand(
            taskId: $id,
            title: $validated['title'] ?? null,
            description: $validated['description'] ?? null,
            status: $validated['status'] ?? null,
        );

        $this->commandBus->dispatch($command);

        return response()->json(['message' => 'Task updated']);
    }

    public function destroy(int $id): JsonResponse
    {
        $command = new DeleteTaskCommand($id);
        $this->commandBus->dispatch($command);

        return response()->json(null, 204);
    }
}
```

#### Этап 4: Настройка Event/Listener биндингов

**4.1 EventServiceProvider**

```php
// app/Providers/EventServiceProvider.php
namespace App\Providers;

use App\Events\Task\TaskCreatedEvent;
use App\Events\Task\TaskUpdatedEvent;
use App\Events\Task\TaskDeletedEvent;
use App\EventHandlers\Task\UpdateTaskReadModelHandler;
use App\EventHandlers\Task\SendTaskNotificationHandler;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        TaskCreatedEvent::class => [
            UpdateTaskReadModelHandler::class,
            SendTaskNotificationHandler::class,
        ],
        TaskUpdatedEvent::class => [
            UpdateTaskReadModelHandler::class,
        ],
        TaskDeletedEvent::class => [
            UpdateTaskReadModelHandler::class,
        ],
    ];
}
```

#### Этап 5: Миграция Read Model

```php
// database/migrations/xxxx_create_task_read_models_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_read_models', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('team_id')->nullable();

            // Denormalized data for fast reads
            $table->string('user_name')->nullable();
            $table->string('team_name')->nullable();
            $table->integer('comments_count')->default(0);

            $table->timestamps();

            // Indexes for fast queries
            $table->index('user_id');
            $table->index('team_id');
            $table->index('status');
            $table->index(['status', 'user_id']);
        });
    }
};
```

#### Этап 6: Запуск Worker'ов

```bash
# Запуск queue worker для RabbitMQ
docker compose exec php php artisan queue:work rabbitmq --queue=default,read-model-sync

# Или через Supervisor (рекомендуется для production)
```

**Конфигурация Supervisor:**

```ini
; /etc/supervisor/conf.d/laravel-worker.conf
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/artisan queue:work rabbitmq --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/html/storage/logs/worker.log
stopwaitsecs=3600
```

---

## 6. Оценка трудозатрат

### 6.1 Разбивка по этапам

| Этап | Задачи | Сложность |
|------|--------|-----------|
| **1. Инфраструктура** | Docker, RabbitMQ, конфиги | Низкая |
| **2. Базовые классы** | Command, Query, Event, Bus | Средняя |
| **3. Tasks CQRS** | Рефакторинг Tasks модуля | Высокая |
| **4. Comments CQRS** | Рефакторинг Comments | Средняя |
| **5. Teams CQRS** | Рефакторинг Teams | Средняя |
| **6. Auth CQRS** | Рефакторинг Auth | Средняя |
| **7. Read Models** | Миграции, синхронизация | Высокая |
| **8. Тестирование** | Unit + Integration тесты | Высокая |
| **9. Документация** | API docs, архитектура | Низкая |

### 6.2 Необходимые навыки команды

- Понимание CQRS и Event-Driven Architecture
- Опыт работы с RabbitMQ / Message Brokers
- Laravel Events, Listeners, Queues
- Асинхронное программирование

### 6.3 Риски и митигация

| Риск | Вероятность | Митигация |
|------|-------------|-----------|
| Eventual Consistency проблемы | Высокая | Тщательное проектирование, таймауты |
| Сложность отладки | Средняя | Logging, tracing (Jaeger/Zipkin) |
| Overhead для простых операций | Средняя | Оставить простые CRUD синхронными |
| Дублирование данных | Высокая | Чёткие правила синхронизации |

---

## Заключение

Текущая архитектура проекта представляет собой **качественную Layered Architecture** с правильным применением паттернов Repository и Service Layer. Переход на CQRS целесообразен при:

1. Росте нагрузки на систему
2. Необходимости независимого масштабирования чтения/записи
3. Требованиях к аудиту и истории изменений
4. Планах перехода на микросервисы

Миграция должна проводиться **инкрементально**, начиная с наиболее нагруженных модулей (Tasks), с сохранением обратной совместимости API.
