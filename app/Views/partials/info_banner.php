<?php
/**
 * Info banner collapsible — panduan singkat di atas form input (Pek 2 v2).
 *
 * Pakai:
 *   <?= view('partials/info_banner', [
 *       'judul'   => 'Cara Mengisi Capaian Kompetensi',
 *       'langkah' => ['Langkah 1 ...', 'Langkah 2 ...'],   // boleh berisi HTML
 *       'tips'    => 'Teks tips opsional',                 // opsional
 *   ]) ?>
 */
$judul   = $judul   ?? 'Panduan Pengisian';
$langkah = $langkah ?? [];
$tips    = $tips    ?? '';
?>
<div class="alert alert-info border-0 shadow-sm mb-3">
    <details>
        <summary style="cursor:pointer;list-style:none;">
            <i class="bi bi-info-circle-fill me-1"></i>
            <strong><?= esc($judul) ?></strong>
            <small class="text-muted ms-1">(klik untuk lihat panduan)</small>
        </summary>
        <div class="mt-2">
            <?php if (!empty($langkah)): ?>
                <ol class="mb-2 small">
                    <?php foreach ($langkah as $l): ?>
                        <li><?= $l ?></li>
                    <?php endforeach; ?>
                </ol>
            <?php endif; ?>
            <?php if ($tips !== ''): ?>
                <p class="small mb-1"><i class="bi bi-lightbulb me-1"></i><strong>Tips:</strong> <?= esc($tips) ?></p>
            <?php endif; ?>
            <a href="<?= base_url('help/panduan-rapor') ?>" class="small">
                <i class="bi bi-question-circle me-1"></i>Lihat panduan lengkap
            </a>
        </div>
    </details>
</div>
