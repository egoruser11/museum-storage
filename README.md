# Музейное хранилище

Laravel-приложение для курсовой работы: учет музейного фонда, пользовательская передача предметов в музей и заявки на выкуп предметов.

## Функционал

- Публичная часть: главное меню, каталог предметов, поиск, фильтр по категории, карточка экспоната.
- Пользовательская часть: регистрация, вход, отправка предмета в дар или на продажу музею, просмотр своих заявок, оформление выкупа доступного предмета.
- Админ-часть: отдельный доступ по роли `admin`, CRUD категорий и экспонатов, рассмотрение заявок на передачу, обработка заявок на выкуп, отчеты.
- API на Laravel Sanctum: регистрация, вход, токен, список экспонатов, личные заявки и выкупы.
- База данных PostgreSQL в Docker Compose.

## Таблицы

В предметной области используется компактная схема:

- `users`
- `categories`
- `artifacts`
- `artifact_submissions`
- `purchase_orders`
- `personal_access_tokens`

## Запуск

```bash
docker compose up -d --build
docker compose exec app php artisan migrate:fresh --seed
```

Приложение доступно по адресу:

```text
http://localhost:8890
```

PostgreSQL проброшен на порт `5434`.

## Пользователи

После выполнения `php artisan migrate:fresh --seed` создаются следующие тестовые пользователи:

| Пользователь | Email | Роль | Пароль | Доступ |
|---|---|---|---|---|
| Администратор музея | `admin@museum.test` | `admin` | `password` | Админ-панель, категории, экспонаты, пользователи, рассмотрение заявок и отчеты |
| Иван Коллекционер | `user@museum.test` | `user` | `password` | Каталог, передача предметов музею, заявки на выкуп и просмотр своих заявок |

Для всех тестовых пользователей используется пароль `password`.

## Проверка

```bash
php artisan test
```

В проекте есть feature-тесты для публичных страниц, пользовательского workflow, админского workflow и Sanctum API.

## API

Публичные endpoints:

- `POST /api/register`
- `POST /api/login`
- `GET /api/artifacts`

Endpoints с Bearer token:

- `GET /api/user`
- `POST /api/logout`
- `GET /api/submissions`
- `POST /api/submissions`
- `GET /api/orders`
- `POST /api/artifacts/{artifact}/orders`
