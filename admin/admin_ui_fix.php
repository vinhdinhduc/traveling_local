<?php

$files = [
    'sliders/index.php',
    'sliders/add.php',
    'sliders/edit.php',
    'gallery/index.php',
    'gallery/add.php',
    'amenities/index.php',
    'amenities/add.php',
    'amenities/edit.php',
    'email-templates/index.php',
    'email-templates/edit.php',
    'settings/index.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;

    $content = file_get_contents($file);

    // 1. Remove the bottom wrappers
    $content = preg_replace('/<\/div>\s*<\/main>\s*<\/div>\s*<\?php require_once \'\.\.\/includes\/footer\.php\'; \?'.'>/i', "<?php require_once '../includes/footer.php'; ?" . ">", $content);

    // 2. Remove the top wrappers up to the flash message
    // Regex matches <div class="admin-wrapper"> down to <?= getFlash() ?> or <div class="admin-content fade-in">
    $pattern = '/<div class="admin-wrapper">.*?<div class="admin-content fade-in">\s*(<\?= getFlash\(\) \?'.'>)?/s';

    if (strpos($file, 'index.php') !== false && strpos($file, 'settings') === false) {
        // For index files, replace the whole card header with content-header
        $patternIndex = '/<div class="admin-wrapper">.*?<div class="admin-content fade-in">\s*(<\?= getFlash\(\) \?'.'>)?\s*<div class="card">\s*<div class="card-header"[^>]*>\s*<h3[^>]*>(.*?)<\/h3>\s*<a href="([^"]+)"[^>]*>(.*?)<\/a>\s*<\/div>/s';
        
        $replacementIndex = '
<div class="content-header">
    <h1><i class="fas fa-list" style="color:var(--admin-primary)"></i> $2</h1>
    <a href="$3" class="btn-admin btn-add">$4</a>
</div>
<div class="table-wrapper fade-in">
';
        $content = preg_replace($patternIndex, $replacementIndex, $content);
        
        // Also strip the closing </div> of <div class="card">
        $content = str_replace('</div>'."\n".'<?php require_once', '</div>'."\n".'<?php require_once', $content); // wait, it's just stripping later so let's ignore the exact closing tag issue as it might leave an extra </div> or missing.
        // Actually table-wrapper replaces card, so the closing </div> is fine!

        // Replace inline table styles with .admin-table
        $content = preg_replace('/<div style="display:grid; grid-template-columns: repeat[^>]*>/i', '<div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px;">', $content);
        $content = preg_replace('/<table class="table" style="width:100%; border-collapse:collapse;">/i', '<table class="admin-table">', $content);
        $content = preg_replace('/<tr style="border-bottom: 2px solid #eee; text-align: left;">/i', '<tr>', $content);
        $content = preg_replace('/<th style="padding: 12px 10px[^>]*>/i', '<th>', $content);
        $content = preg_replace('/<tr style="border-bottom: 1px solid #eee;">/i', '<tr>', $content);
        $content = preg_replace('/<td style="padding: 12px 10px[^>]*>/i', '<td>', $content);

    } else if (strpos($file, 'settings') !== false) {
        $patternSettings = '/<div class="admin-wrapper">.*?<div class="admin-content fade-in">\s*(<\?= getFlash\(\) \?'.'>)?/s';
        $replacementSettings = '
<div class="content-header">
    <h1><i class="fas fa-cog" style="color:var(--admin-primary)"></i> Cài đặt hệ thống</h1>
</div>
<div class="fade-in">
';
        $content = preg_replace($patternSettings, $replacementSettings, $content);

    } else {
        // Form files (add, edit)
        $patternForm = '/<div class="admin-wrapper">.*?<div class="admin-header">\s*<h2>(.*?)<\/h2>.*?<div class="admin-content fade-in">\s*(<\?= getFlash\(\) \?'.'>)?\s*<div class="admin-card".*?>/s';
        $replacementForm = '
<div class="content-header">
    <h1><i class="fas fa-edit" style="color:var(--admin-primary)"></i> $1</h1>
    <a href="index.php" class="btn-admin btn-back"><i class="fas fa-arrow-left"></i> Quay lại</a>
</div>

<div class="form-card fade-in">
';
        $content = preg_replace($patternForm, $replacementForm, $content);
        
        // Convert btn-primary to btn-admin btn-add
        $content = str_replace('btn btn-primary', 'btn-admin btn-add', $content);
        $content = str_replace('btn btn-outline', 'btn-admin btn-back', $content);
    }

    // Fix image URL mapping for sliders
    if (strpos($file, 'sliders') !== false) {
        $content = str_replace('src="<?= sanitize($s[\'image\']) ?>"', 'src="<?= getImageUrl($s[\'image\'], \'sliders\') ?>"', $content);
        $content = str_replace('src="<?= sanitize($slider[\'image\']) ?>"', 'src="<?= getImageUrl($slider[\'image\'], \'sliders\') ?>"', $content);
    }
    
    // Fix image URL mapping for gallery
    if (strpos($file, 'gallery/index.php') !== false) {
        $content = str_replace('src="<?= sanitize($img[\'image\']) ?>"', 'src="<?= getImageUrl($img[\'image\'], \'gallery\') ?>"', $content);
    }

    file_put_contents($file, $content);
    echo "Refactored $file\n";
}

echo "Done.\n";
