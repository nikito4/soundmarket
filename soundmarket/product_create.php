<?php
declare(strict_types=1);
require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/functions.php';

require_login();

$pdo = db();
$user_id = current_user_id();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type          = post('type');
    $title         = post('title');
    $description   = post('description');
    $price_str     = post('price');
    $genre         = post('genre');
    $bpm_str       = post('bpm');
    $delivery_str  = post('delivery_days');

    $is_service = in_array($type, ['service', 'digital'], true);

    // --- Валидация ---
    $allowed_types = ['beat', 'music', 'service', 'digital'];
    if (!in_array($type, $allowed_types, true)) {
        $error = 'Невалиден тип на продукта.';
    } elseif (mb_strlen($title) < 2) {
        $error = 'Заглавието трябва да е поне 2 символа.';
    } elseif (!is_numeric($price_str) || (float)$price_str < 0) {
        $error = 'Въведете валидна цена (0 или повече).';
    } elseif ($is_service && $delivery_str !== '' && (!ctype_digit($delivery_str) || (int)$delivery_str < 1)) {
        $error = 'Срокът трябва да е цяло положително число дни.';
    }

    // --- Качване на основния файл (само за beat/music) ---
    $file_path = null;
    if (!$error && !$is_service && !empty($_FILES['file']['name'])) {
        $allowed_ext = ['mp3', 'wav', 'flac', 'zip'];
        $orig_name   = $_FILES['file']['name'];
        $ext         = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed_ext, true)) {
            $error = 'Файлът трябва да е MP3, WAV, FLAC или ZIP.';
        } elseif ($_FILES['file']['size'] > 100 * 1024 * 1024) {
            $error = 'Файлът не може да е по-голям от 100 MB.';
        } else {
            $uploads_dir = __DIR__ . '/uploads/';
            $safe_name   = uniqid('file_', true) . '.' . $ext;
            if (!move_uploaded_file($_FILES['file']['tmp_name'], $uploads_dir . $safe_name)) {
                $error = 'Грешка при качване на файла. Проверете правата на папката uploads/.';
            } else {
                $file_path = 'uploads/' . $safe_name;
            }
        }
    }

    // --- Качване на корицата ---
    $cover_path = null;
    if (!$error && !empty($_FILES['cover']['name'])) {
        $allowed_img = ['jpg', 'jpeg', 'png', 'webp'];
        $orig_name   = $_FILES['cover']['name'];
        $ext         = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed_img, true)) {
            $error = 'Корицата трябва да е JPG, PNG или WebP.';
        } elseif ($_FILES['cover']['size'] > 5 * 1024 * 1024) {
            $error = 'Корицата не може да е по-голяма от 5 MB.';
        } else {
            $covers_dir = __DIR__ . '/uploads/covers/';
            if (!is_dir($covers_dir)) {
                mkdir($covers_dir, 0755, true);
            }
            $safe_name = uniqid('cover_', true) . '.' . $ext;
            if (!move_uploaded_file($_FILES['cover']['tmp_name'], $covers_dir . $safe_name)) {
                $error = 'Грешка при качване на корицата. Проверете правата на папката uploads/covers/.';
                if ($file_path && file_exists(__DIR__ . '/' . $file_path)) {
                    unlink(__DIR__ . '/' . $file_path);
                    $file_path = null;
                }
            } else {
                $cover_path = 'uploads/covers/' . $safe_name;
            }
        }
    }

    // --- Запис в базата данни ---
    if (!$error) {
        $price_eur     = (int)round((float)$price_str * 100);
        $bpm           = (!$is_service && $bpm_str !== '') ? (int)$bpm_str : null;
        $delivery_days = ($is_service && $delivery_str !== '') ? (int)$delivery_str : null;

        $stmt = $pdo->prepare("
            INSERT INTO products (owner_id, type, title, description, price_eur, genre, bpm, delivery_days, file_path, cover_path)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $user_id,
            $type,
            $title,
            $description,
            $price_eur,
            (!$is_service && $genre !== '') ? $genre : null,
            $bpm,
            $delivery_days,
            $file_path,
            $cover_path,
        ]);

        $new_id = (int)$pdo->lastInsertId();
        redirect('product.php?id=' . $new_id);
    }
}

$title = 'Качи оферта — ' . APP_NAME;
require __DIR__ . '/inc/header.php';
?>

<div class="container" style="padding: 60px 0;">
    <div style="max-width: 700px; margin: 0 auto;">
        <div class="page-head" style="text-align: center; margin-bottom: 30px;">
            <h2>Създай нова оферта</h2>
            <p class="muted">Сподели своето изкуство със света.</p>
        </div>

        <form method="post" enctype="multipart/form-data" class="card" style="padding: 40px; display: grid; gap: 20px;" id="create-form">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-size: 14px; color: var(--muted);">Тип на продукта</label>
                    <select name="type" required id="product-type" onchange="toggleTypeFields()">
                        <option value="beat"    <?= post('type') === 'beat'    ? 'selected' : '' ?>>Бийт (Beat)</option>
                        <option value="music"   <?= post('type') === 'music'   ? 'selected' : '' ?>>Музика / Песен</option>
                        <option value="service" <?= post('type') === 'service' ? 'selected' : '' ?>>Услуга (Mix/Master)</option>
                        <option value="digital" <?= post('type') === 'digital' ? 'selected' : '' ?>>Дигитален пакет</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 8px; font-size: 14px; color: var(--muted);">Заглавие</label>
                    <input name="title" required placeholder="Напр. Dark Trap Beat" value="<?= h(post('title')) ?>">
                </div>
            </div>

            <!-- Описание — винаги се показва, но label-ът се сменя -->
            <div>
                <label id="desc-label" style="display: block; margin-bottom: 8px; font-size: 14px; color: var(--muted);">Описание</label>
                <textarea
                    name="description"
                    id="desc-field"
                    rows="4"
                    placeholder="Разкажи ни повече..."
                    style="width: 100%; max-width: 100%; min-height: 100px; resize: vertical; display: block;"
                ><?= h(post('description')) ?></textarea>
            </div>

            <!-- Цена — винаги се показва -->
            <div style="max-width: 340px;">
                <label style="display: block; margin-bottom: 8px; font-size: 14px; color: var(--muted);">Цена (€ EUR)</label>
                <input name="price" type="number" step="0.01" min="0" required placeholder="0.00" value="<?= h(post('price')) ?>">
            </div>

            <!-- ===== ПОЛЕТА ЗА БИЙТ / МУЗИКА ===== -->
            <div id="beat-music-fields" style="display: grid; gap: 20px;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-size: 14px; color: var(--muted);">Жанр</label>
                        <input name="genre" placeholder="Trap, Drill, Pop..." value="<?= h(post('genre')) ?>">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-size: 14px; color: var(--muted);">BPM</label>
                        <input name="bpm" type="number" min="0" placeholder="Напр. 140" value="<?= h(post('bpm')) ?>">
                    </div>
                </div>

                <div>
                    <label style="display: block; margin-bottom: 8px; font-size: 14px; color: var(--muted);">Основен файл (MP3/WAV/FLAC/ZIP)</label>
                    <input name="file" type="file" accept=".mp3,.wav,.flac,.zip" style="padding: 8px;">
                </div>
            </div>

            <!-- ===== ПОЛЕТА ЗА УСЛУГА / ДИГИТАЛЕН ПАКЕТ ===== -->
            <div id="service-digital-fields" style="display: none; gap: 20px; flex-direction: column;">

                <div style="background: rgba(124,58,237,0.08); border: 1px solid rgba(124,58,237,0.2); border-radius: 12px; padding: 16px 20px;">
                    <p style="margin: 0; font-size: 13px; color: var(--muted); line-height: 1.6;">
                        Опиши услугата в полето „Какво включва" по-горе. Можеш да изброиш всичко — брой ревизии, формати на доставка, бонуси и т.н.
                    </p>
                </div>

                <div style="max-width: 340px;">
                    <label style="display: block; margin-bottom: 8px; font-size: 14px; color: var(--muted);">Срок на изпълнение (работни дни)</label>
                    <input name="delivery_days" id="delivery-days" type="number" min="1" max="365"
                           placeholder="Напр. 5" value="<?= h(post('delivery_days')) ?>">
                    <small class="muted" style="display: block; margin-top: 5px;">* Оставете празно ако срокът е по договаряне.</small>
                </div>
            </div>

            <!-- Корица — за всички типове -->
            <div>
                <label style="display: block; margin-bottom: 8px; font-size: 14px; color: var(--muted);">Корица (JPG/PNG/WebP)</label>
                <input name="cover" type="file" accept="image/*" style="padding: 8px;">
                <small class="muted" style="display: block; margin-top: 5px;">* Ако не качите корица, ще се използва изображение по подразбиране.</small>
            </div>

            <?php if ($error): ?>
                <div style="background: rgba(240, 68, 56, 0.1); color: #f04438; padding: 15px; border-radius: 12px; font-size: 14px; border: 1px solid rgba(240, 68, 56, 0.2);">
                    &#x26A0;&#xFE0F; <?= h($error) ?>
                </div>
            <?php endif; ?>

            <button class="btn primary" type="submit" style="padding: 18px; font-weight: bold; font-size: 1.1rem; margin-top: 10px;">
                Публикувай офертата
            </button>
        </form>
    </div>
</div>

<script>
function toggleTypeFields() {
    var type = document.getElementById('product-type').value;
    var isService = (type === 'service' || type === 'digital');

    var beatFields    = document.getElementById('beat-music-fields');
    var serviceFields = document.getElementById('service-digital-fields');
    var descLabel     = document.getElementById('desc-label');
    var descField     = document.getElementById('desc-field');

    if (isService) {
        beatFields.style.display    = 'none';
        serviceFields.style.display = 'flex';
        descLabel.textContent       = 'Какво включва';
        descField.placeholder       = 'Напр. Мастеринг на 1 песен, до 2 ревизии, доставка в WAV и MP3...';
    } else {
        beatFields.style.display    = 'grid';
        serviceFields.style.display = 'none';
        descLabel.textContent       = 'Описание';
        descField.placeholder       = 'Разкажи ни повече...';
    }
}

// Прилагаме при зареждане на страницата (важно при грешка и reload)
toggleTypeFields();
</script>

<?php require __DIR__ . '/inc/footer.php'; ?>
