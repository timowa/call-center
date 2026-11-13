# Учебное приложение операторов 112

## Docker (локальная разработка)

Требуется Docker Desktop с Linux-контейнерами и Docker Compose. PHP, Composer,
Node.js и MySQL на компьютере устанавливать не нужно. Используются PHP 8.4
(требуется пакетами из composer.lock), Node.js 22 и MySQL 8.4.

Первый запуск из корня проекта (PowerShell):

```powershell
Copy-Item .env.example .env # только если .env ещё нет
docker compose up -d --build
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
docker compose --profile queue up -d queue
```

Первый запуск скачивает образы и устанавливает зависимости из lock-файлов.
Приложение: http://localhost:8000. Vite: http://localhost:5173.
Сидер запускайте один раз на новой БД: повторный запуск может создать дубликаты.
Обработчик очереди включайте после миграций, поскольку очередь хранится в БД.

Для существующего `.env` сохраните APP_KEY и остальные нужные настройки;
настройте DB_DATABASE, DB_USERNAME, DB_PASSWORD и MYSQL_ROOT_PASSWORD по шаблону.
В PHP-контейнерах соединение использует MySQL по адресу mysql:3306.
Не задавайте DB_URL: он имеет приоритет над отдельными параметрами соединения.
Пароли шаблона предназначены только для локальной разработки.

MySQL доступен с компьютера по адресу `127.0.0.1:3307`, база и пользователь
по умолчанию — `call_center`, пароль — значение DB_PASSWORD из `.env`.
Порты меняются через APP_PORT, VITE_PORT и FORWARD_DB_PORT; при смене APP_PORT
обновите APP_URL. Vite и HMR рассчитаны на браузер на этом компьютере.

Повторный запуск и остановка:

```powershell
docker compose --profile queue up -d
docker compose --profile queue down
```

Данные MySQL сохраняются в именованном томе после остановки и пересоздания
контейнеров. Команда `down -v` удаляет тома, включая БД — для обычной остановки
её не используйте. Изменение паролей в `.env` не меняет уже созданных пользователей MySQL.

Исходники подключены с компьютера; vendor и node_modules находятся в отдельных
Linux-томах. Vite использует polling для отслеживания изменений на Windows.

Полезные команды:

```powershell
docker compose logs -f app vite mysql
docker compose exec app php artisan migrate
docker compose exec app composer install
docker compose exec vite npm ci
docker compose exec vite npm run build
docker compose exec -e DB_CONNECTION=sqlite -e DB_DATABASE=:memory: app php artisan test
```

Тесты запускаются с SQLite в памяти, отдельно от рабочей MySQL.
Для проверки собранного frontend остановите Vite и удалите `public/hot`, если
он остался после остановки; затем выполните сборку через
`docker compose run --rm --no-deps vite npm run build`.

## Администрирование справочников

Админка открывается только по прямой ссылке `/admin`. Вход использует отдельную
таблицу `admins` и guard `admin`; учётные записи операторов доступа не дают.
В боковой панели находятся разделы «УКИО», «01» и «Газ». Объекты общие для УКИО и 01.
Созданные значения доступны при открытии карточки; уже открытую карточку нужно
переоткрыть. Используемые в карточках записи удалить нельзя.

После применения миграций создайте администратора (пароль запрашивается скрыто):

```powershell
docker compose exec app php artisan admin:create admin
```

Для генерации случайного пароля: `docker compose exec app php artisan admin:create admin --generate`.
Команда не перезаписывает существующую учётную запись. Пароли в репозитории не хранятся.

## Запуск без Docker

Установите PHP 8.4 с расширениями проекта, Composer, Node.js 22 и MySQL.
Создайте `.env` из `.env.example`, укажите DB_HOST=127.0.0.1, порт и реквизиты БД.
Выполните `composer install`, `npm ci`, `php artisan key:generate`,
`php artisan migrate` и, для новой БД, `php artisan db:seed`.
Запустите `php artisan serve` и `npm run dev` в отдельных терминалах.
