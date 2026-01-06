<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

echo "=== ДЕМОНСТРАЦИЯ КЕШИРОВАНИЯ ===\n\n";

// 1. Проверяем записи в таблице cache
echo "1. Содержимое таблицы cache:\n";
$cacheItems = DB::table('cache')->get();
if ($cacheItems->count() > 0) {
    foreach ($cacheItems as $item) {
        echo "   - Ключ: {$item->key}\n";
    }
} else {
    echo "   Кеш пуст\n";
}

echo "\n2. Добавляем тестовое значение в кеш...\n";
Cache::put('demo_key', 'Это демонстрационное значение', 3600);
echo "   ✓ Добавлено: demo_key = 'Это демонстрационное значение'\n";

echo "\n3. Читаем значение из кеша:\n";
$value = Cache::get('demo_key');
echo "   Значение: {$value}\n";

echo "\n4. Проверяем кеш статей (если есть):\n";
$articlesCache = Cache::get('articles_page_1');
if ($articlesCache) {
    echo "   ✓ Кеш главной страницы существует\n";
    echo "   Количество статей: " . $articlesCache->count() . "\n";
} else {
    echo "   Кеш главной страницы пуст (откройте главную страницу в браузере)\n";
}

echo "\n5. Очищаем тестовый ключ:\n";
Cache::forget('demo_key');
echo "   ✓ Ключ 'demo_key' удален\n";

echo "\n=== ЗАВЕРШЕНО ===\n";
