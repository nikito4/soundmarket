# SoundMarket Admin Panel

## Инсталация

### 1. Стартирай XAMPP
Увери се, че Apache и MySQL работят.

### 2. Изпълни SQL файла
В phpMyAdmin отвори базата `soundmarket` и изпълни:
```
htdocs/soundmarket/admin/setup_admin.sql
```
Или от командния ред:
```bash
mysql -u root soundmarket < setup_admin.sql
```
Това създава таблиците `admin_users` и `activity_logs`.

### 3. Влез в панела
Отвори: **http://localhost/soundmarket/admin/**

| Поле | Стойност |
|------|----------|
| Потребител | `admin` |
| Парола | `admin123` |

> ⚠ **Смени паролата веднага** след първото влизане: Настройки → Смяна на парола.

---

## Структура на файловете

```
admin/
├── index.php           → Redirect към dashboard
├── login.php           → Форма за вход
├── logout.php          → Изход и изчистване на сесия
├── dashboard.php       → Основен преглед (метрики + графика)
├── users.php           → Управление на потребители
├── products.php        → Управление на продукти
├── orders.php          → Управление на поръчки
├── logs.php            → Лог на действията
├── settings.php        → Смяна на парола
├── includes/
│   ├── db.php          → PDO връзка (localhost / soundmarket / root)
│   ├── auth.php        → Сесии, login/logout функции
│   ├── functions.php   → format_bgn(), log_action(), badges, CSRF
│   ├── sidebar.php     → Странична навигация
│   ├── header.php      → HTML head + topbar
│   └── footer.php      → Затваряне на HTML
├── assets/
│   ├── style.css       → Пълни стилове (light + dark mode)
│   └── script.js       → Sidebar toggle, dark mode, потвърждения
└── setup_admin.sql     → SQL за нови таблици + default admin
```

---

## Функционалност

| Страница | Какво прави |
|----------|-------------|
| Dashboard | Метрики (потребители, продукти, поръчки, приход), бар графика за 6 месеца, топ продукти, последни 10 поръчки |
| Потребители | Пагиниран списък, търсене, модал с детайли и продукти, изтриване (CASCADE) |
| Продукти | Списък с филтър по тип, inline редакция на цена, изтриване с файлове |
| Поръчки | Expand rows с order items, промяна на статус, филтър по статус |
| Логове | Таблица с всички admin действия |
| Настройки | Смяна на парола, списък с администратори (само superadmin) |

---

## Сигурност
- PDO prepared statements навсякъде
- CSRF токен на всеки POST форм
- `session_regenerate_id(true)` при вход
- `htmlspecialchars()` на всеки output
- Всяко действие се логва в `activity_logs`
