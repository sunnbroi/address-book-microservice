# 📦 Address Book Microservice

Laravel-микросервис для управления адресными книгами, получателями и отправки сообщений через Telegram Bot API. Поддерживает HMAC-подпись, очереди и вебхуки.

---

## 🚀 Быстрый старт

### 1. 📥 Клонировать репозиторий и установить зависимости

```bash
git clone https://github.com/your-user/address-book-microservice.git
cd address-book-microservice
composer install
2. ⚙️ Настроить .env
Создай .env:

cp .env.example .env
php artisan key:generate
Обязательно укажи:

env
APP_URL=http://127.0.0.1:8000
TELEGRAM_BOT_TOKEN=your_token
TELEGRAM_WEBHOOK_URL=https://your-domain.com/api/webhook
3. 🧩 Запустить миграции

php artisan migrate
📊 Наполнение базы
🔹 Для разработки (минимально)

php artisan db:seed --class=ClientsTableSeeder
Создаст одного клиента с client_key и secret_key.

🔸 Для тестов (полная загрузка фабрик и сидеров)

php artisan db:seed

🤖 Установка Telegram Webhook
После настройки .env:

php artisan telegram:webhook
Webhook будет привязан к TELEGRAM_WEBHOOK_URL.

📬 Postman
Коллекции и окружения для тестирования API лежат в папке postman/:

📁 Содержимое
address-book-collection.json — все запросы

address-book-env.json — шаблон окружения (без секретов)

userTestClient.postman_environment.json — окружение с ключами (использовать локально)

🛠 Как использовать
Импортируй .json в Postman

Введи значения переменных:

client_key, secret_key, base_url, address_book_id, recipient_id

Все подписи HMAC рассчитываются автоматически в prerequest-скриптах.

⚙️ Сборка проекта (деплой)
Смотри скрипт deploy.sh:

bash
Копировать
Редактировать
./deploy.sh
Выполняет:

git pull

composer install

миграции

кеширование конфигурации и маршрутов

 Автор:https://github.com/sunnbroi