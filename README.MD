# VK OAuth PHP Implementation

Простая реализация OAuth аутентификации через VK ID (id.vk.com) на чистом PHP с соблюдением принципов SOLID.

## Описание проекта

Проект реализует OAuth 2.0 аутентификацию через VK ID с использованием:
- PKCE (Proof Key for Code Exchange) для безопасности
- CSRF защита через state параметр
- Безопасное хранение сессий
- Чистая архитектура с разделением ответственности

## Требования

- PHP 8.1 или выше
- Composer
- Зарегистрированное приложение в VK ID (id.vk.com)
- Веб-сервер (Apache/Nginx) или встроенный PHP-сервер

## Установка

1. Клонируйте репозиторий:
```bash
git clone [url-репозитория]
cd oAuthPHP
```

2. Установите зависимости:
```bash
composer install
```

3. Создайте файл конфигурации:
```bash
cp config/vk-config.example.php config/vk-config.php
```

4. Отредактируйте `config/vk-config.php` и добавьте ваши данные:
- client_id - ID вашего приложения в VK ID
- client_secret - Секретный ключ приложения
- redirect_uri - URL для callback (должен совпадать с настройками в VK ID)

## Запуск проекта

### Через встроенный PHP-сервер (для разработки)

```bash
php -S localhost:8000 -t public/
```

### Через Apache

1. Настройте виртуальный хост:
```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /path/to/oAuthPHP/public
    
    <Directory /path/to/oAuthPHP/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

2. Создайте .htaccess в папке public:
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

### Через Nginx

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/oAuthPHP/public;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
}
```

## Структура проекта

```
├── config/             # Конфигурационные файлы
│   ├── vk-config.example.php  # Пример конфигурации
│   └── vk-config.php         # Ваша конфигурация (не в git)
├── public/            # Публичная директория
│   ├── index.php     # Точка входа
│   ├── login.php     # Обработка входа
│   └── oauth/        # OAuth callback
├── src/              # Исходный код
│   ├── Core/         # Ядро приложения
│   └── VkOAuth.php   # VK OAuth реализация
├── templates/        # Шаблоны
│   └── welcome.php   # Главная страница
└── vendor/           # Зависимости
```

## Безопасность

- Все конфиденциальные данные хранятся в конфигурационном файле
- Конфигурационный файл исключен из git через .gitignore
- Используются безопасные сессии
- Реализована CSRF защита через state параметр
- Используется PKCE для защиты от перехвата кода
- Все пользовательские данные экранируются при выводе

## API VK ID

Проект использует VK ID API (id.vk.com):
- Авторизация: https://id.vk.com/authorize
- Получение токена: https://id.vk.com/oauth2/auth
- Информация о пользователе: https://id.vk.com/oauth2/user_info

## Лицензия

MIT
