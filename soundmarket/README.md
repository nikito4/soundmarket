# SoundMarket — Уеб платформа за музикални услуги и дигитални продукти

## Изисквания

- XAMPP (Apache + MySQL)
- PHP 8.2 или по-нова версия
- Браузър (Chrome, Firefox, Edge)

---

## Инсталация и стартиране

### 1. Копиране на файловете
Постави папката `soundmarket` в:
C:\xampp\htdocs\soundmarket

### 2. Стартиране на XAMPP
Отвори **XAMPP Control Panel** и стартирай:
- **Apache** → Start
- **MySQL** → Start

### 3. Създаване на базата данни
1. Отвори браузър и влез на `http://localhost/phpmyadmin`
2. Кликни **New** (вляво) → въведи име `soundmarket` → **Create**
3. Избери базата `soundmarket` от лявото меню
4. Кликни таб **SQL**
5. Постави съдържанието на файла `sql/soundmarket.sql`
6. Натисни **Go**

### 4. Конфигурация на Stripe (по избор)
Отвори файла `inc/.env` и замени ключовете:
STRIPE_PUBLIC_KEY=pk_test_ТВОЯ_КЛЮЧ
STRIPE_SECRET_KEY=sk_test_ТВОЯ_КЛЮЧ
Ключовете се намират на `https://dashboard.stripe.com/apikeys`

### 5. Отваряне на сайта
Влез на адрес:
http://localhost/soundmarket

---

## Структура на проекта
soundmarket/
├── inc/                    # Конфигурация и помощни файлове
│   ├── config.php          # Константи и настройки
│   ├── db.php              # PDO връзка с базата данни
│   ├── auth.php            # Автентикация и сесии
│   ├── functions.php       # Помощни функции
│   └── .env                # Stripe ключове (не се качва в Git)
├── admin/                  # Административен панел
│   ├── login.php           # Вход за администратор
│   ├── dashboard.php       # Главно табло
│   ├── orders.php          # Управление на поръчки
│   ├── products.php        # Управление на продукти
│   └── users.php           # Управление на потребители
├── payment/                # Stripe интеграция
│   ├── create-checkout-session.php
│   ├── success.php
│   ├── cancel.php
│   └── webhook.php
├── assets/
│   ├── css/style.css       # Главен stylesheet
│   └── js/                 # JavaScript файлове
├── uploads/                # Качени файлове
│   ├── covers/             # Обложки на продукти
│   ├── avatars/            # Профилни снимки
│   └── banners/            # Банер снимки
├── sql/
│   └── soundmarket.sql     # SQL дъмп на базата данни
├── index.php               # Начална страница
├── beats.php               # Каталог бийтове
├── music.php               # Каталог музика
├── services.php            # Каталог услуги
├── product.php             # Страница на продукт
├── cart.php                # Количка
├── checkout.php            # Завършване на поръчка
├── dashboard.php           # Потребителски профил
├── profile.php             # Публичен профил
├── profile_edit.php        # Редактиране на профил
├── login.php               # Вход
├── register.php            # Регистрация
└── logout.php              # Изход

---

## Достъп до системата

### Потребителски акаунти (тестови)
| Потребител | Имейл | Парола |
|------------|-------|--------|
| niki | novacaunt13@gmail.com | 123456 |
| beatmaker_bg | beatmaker@gmail.com | password |
| cartoon_beats | cartoon@gmail.com | password |

### Административен панел
http://localhost/soundmarket/admin
| Потребител | Парола |
|------------|--------|
| admin | admin123 |

---

## Тестово плащане с карта (Stripe)

При checkout избери **"Кредитна / Дебитна карта"** и използвай:

| Поле | Стойност |
|------|----------|
| Номер на карта | `4242 4242 4242 4242` |
| Дата | Всяка бъдеща дата |
| CVC | Всякакви 3 цифри |

> Това е тестова карта — не се теглят реални пари.

---

## Честа грешка при стартиране

**Сайтът не се зарежда / бяла страница**
- Провери дали Apache и MySQL са стартирани в XAMPP
- Провери дали папката се казва точно `soundmarket`

**Грешка при базата данни**
- Провери дали базата `soundmarket` е създадена в phpMyAdmin
- Провери дали SQL файлът е изпълнен успешно

**Снимките не се показват**
- Провери дали папките `uploads/covers`, `uploads/avatars`, `uploads/banners` съществуват
- Ако не — създай ги ръчно

---

## Технологии

- **Backend:** PHP 8.2, PDO
- **База данни:** MySQL / MariaDB
- **Frontend:** HTML5, CSS3, JavaScript
- **Плащания:** Stripe API
- **Среда:** XAMPP (localhost)

---

*SoundMarket — Разработено като дипломен проект, 2026 г.*