# 📦 Address Book Microservice

Laravel-микросервис для управления адресными книгами, получателями и отправки сообщений через Telegram Bot API. Поддерживает HMAC-подпись, очереди и вебхуки.

---

## 🚀 Быстрый старт

### 1. 📥 Клонировать репозиторий и установить зависимости

```bash
git clone https://github.com/your-user/address-book-microservice.git
cd address-book-microservice
composer install
```

---

### 2. ⚙️ Настроить .env

Создай `.env`:

```bash
cp .env.example .env
php artisan key:generate
```

Обязательно укажи:

```env
APP_URL=http://127.0.0.1:8000
TELEGRAM_BOT_TOKEN=your_token
TELEGRAM_WEBHOOK_URL=https://your-domain.com/api/webhook
QUEUE_CONNECTION=redis
REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

---
composer require predis/predis
### 3. 🧱 Запустить Redis (в Docker)

Если Redis не установлен на машине — можно запустить через Docker:

```bash
docker run -d -p 6379:6379 --name redis redis
```

> Контейнер работает в фоне и доступен по адресу `127.0.0.1:6379`

При повторном запуске:
```bash
docker start redis

php artisan queue:work
```
---

### 4. 🧩 Запустить миграции

```bash
php artisan migrate
```

---

### 📊 Наполнение базы

🔹 Для разработки (минимально):

```bash
php artisan db:seed --class=ClientsTableSeeder
```

Создаст одного клиента с client_key и secret_key.

🔸 Для тестов (полная загрузка фабрик и сидеров):

```bash
php artisan db:seed
```

---

### 🤖 Установка Telegram Webhook

После настройки `.env`:

```bash
php artisan telegram:webhook
```

Webhook будет привязан к `TELEGRAM_WEBHOOK_URL`.

---

### 📬 Postman

Коллекции и окружения для тестирования API лежат в папке `postman/`:

- `address-book-collection.json` — все запросы
- `address-book-env.json` — шаблон окружения (без секретов)
- `userTestClient.postman_environment.json` — окружение с ключами (использовать локально)

🛠 Как использовать:
- Импортируй `.json` в Postman
- Введи переменные: `client_key`, `secret_key`, `base_url`, `address_book_id`, `recipient_id`
- Все подписи HMAC рассчитываются автоматически в `prerequest`-скриптах

---

### ⚙️ Сборка проекта (деплой)

Смотри скрипт `deploy.sh`:

```bash
./deploy.sh
```

Выполняет:

- `git pull`
- `composer install`
- миграции
- кеширование конфигурации и маршрутов

---

### 🧪 Тестирование

Проект покрыт юнит- и фиче-тестами с использованием PHPUnit.

#### 📦 Установка зависимостей для тестов

```bash
composer install
```

#### ⚙️ Конфигурация тестовой базы данных

В `.env` укажи тестовую БД или создай `.env.testing`:

```env
DB_CONNECTION=mysql
DB_DATABASE=address_book_test
DB_USERNAME=root
DB_PASSWORD=
```

Убедись, что база `address_book_test` существует.

#### 🚀 Запуск всех тестов

```bash
php artisan test
```

Или напрямую:

```bash
vendor/bin/phpunit --testsuite=Feature,Unit
```

#### 💾 Сохранение результатов в файл

```bash
vendor/bin/phpunit --testsuite=Feature,Unit > storage/test-logs/test-output.txt
```

> Убедись, что папка `storage/test-logs/` существует. Если нет:

```bash
mkdir -p storage/test-logs
```

#### ✅ Пример вывода

Смотри актуальный вывод в `storage/test-logs/test-output.txt`.

---

Автор: https://github.com/sunnbroi
