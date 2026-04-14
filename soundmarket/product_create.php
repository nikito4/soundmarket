<?php
declare(strict_types=1);
require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/functions.php';

require_login();

$error = '';
$ok = '';

// ... (тук остава твоят PHP код за обработка на поръчката и качване на файлове) ...

$title = 'Качи оферта — ' . APP_NAME;
require __DIR__ . '/inc/header.php';
?>

<div class="container" style="padding: 60px 0;">
    <div style="max-width: 700px; margin: 0 auto;">
        <div class="page-head" style="text-align: center; margin-bottom: 30px;">
            <h2>Създай нова оферта</h2>
            <p class="muted">Сподели своето изкуство със света.</p>
        </div>

        <form method="post" enctype="multipart/form-data" class="card" style="padding: 40px; display: grid; gap: 20px;">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-size: 14px; color: var(--muted);">Тип на продукта</label>
                    <select name="type" required>
                        <option value="beat">Бийт (Beat)</option>
                        <option value="music">Музика / Песен</option>
                        <option value="service">Услуга (Mix/Master)</option>
                        <option value="digital">Дигитален пакет</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 8px; font-size: 14px; color: var(--muted);">Заглавие</label>
                    <input name="title" required placeholder="Напр. Dark Trap Beat" value="<?= h(post('title')) ?>">
                </div>
            </div>

            <div style="max-width: 100%;"> <label style="display: block; margin-bottom: 8px; font-size: 14px; color: var(--muted);">Описание</label>
                <textarea 
                    name="description" 
                    rows="4" 
                    placeholder="Разкажи ни повече..." 
                    style="width: 100%; max-width: 100%; min-height: 100px; resize: vertical; display: block;"
                ><?= h(post('description')) ?></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-size: 14px; color: var(--muted);">Цена (лв)</label>
                    <input name="price" type="number" step="0.01" min="0" required placeholder="0.00" value="<?= h(post('price')) ?>">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 8px; font-size: 14px; color: var(--muted);">Жанр</label>
                    <input name="genre" placeholder="Trap, Drill, Pop..." value="<?= h(post('genre')) ?>">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-size: 14px; color: var(--muted);">BPM</label>
                    <input name="bpm" type="number" min="0" placeholder="Напр. 140" value="<?= h(post('bpm')) ?>">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 8px; font-size: 14px; color: var(--muted);">Основен файл</label>
                    <input name="file" type="file" style="padding: 8px;">
                </div>
            </div>

            <div>
                <label style="display: block; margin-bottom: 8px; font-size: 14px; color: var(--muted);">Корица (JPG/PNG)</label>
                <input name="cover" type="file" accept="image/*" style="padding: 8px;">
                <small class="muted" style="display: block; margin-top: 5px;">* Ако не качите корица, ще се използва грамофонната плоча по подразбиране.</small>
            </div>

            <?php if ($error): ?>
                <div style="background: rgba(240, 68, 56, 0.1); color: #f04438; padding: 15px; border-radius: 12px; font-size: 14px; border: 1px solid rgba(240, 68, 56, 0.2);">
                    ⚠️ <?= h($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($ok): ?>
                <div style="background: rgba(34, 197, 94, 0.1); color: #4ade80; padding: 15px; border-radius: 12px; font-size: 14px; border: 1px solid rgba(34, 197, 94, 0.2);">
                    ✅ <?= h($ok) ?>
                </div>
            <?php endif; ?>

            <button class="btn primary" type="submit" style="padding: 18px; font-weight: bold; font-size: 1.1rem; margin-top: 10px;">
                Публикувай офертата
            </button>
        </form>
    </div>
</div>

<?php require __DIR__ . '/inc/footer.php'; ?>