<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

echo "=== ПРОВЕРКА КЕША В БД ===\n\n";

// 1. Проверка таблицы cache
echo "1. Таблица cache в БД MySQL (laravel):\n";
try {
    $cacheRecords = DB::table('cache')->get();
    echo "   Количество записей в кеше: " . $cacheRecords->count() . "\n\n";

    if ($cacheRecords->count() > 0) {
        echo "   Ключи кеша:\n";
        foreach ($cacheRecords as $record) {
            $expiration = date('Y-m-d H:i:s', $record->expiration);
            echo "   - {$record->key} (истекает: {$expiration})\n";
        }
    } else {
        echo "   ⚠ Кеш пустой. Это нормально, если вы еще не открывали главную страницу.\n";
    }
} catch (Exception $e) {
    echo "   ❌ Ошибка: " . $e->getMessage() . "\n";
}

echo "\n2. Структура таблицы cache:\n";
try {
    $columns = DB::select("DESCRIBE cache");
    foreach ($columns as $column) {
        echo "   - {$column->Field} ({$column->Type})\n";
    }
} catch (Exception $e) {
    echo "   ❌ Ошибка: " . $e->getMessage() . "\n";
}

echo "\n3. Расположение БД:\n";
echo "   - База данных: " . env('DB_DATABASE') . "\n";
echo "   - Хост: " . env('DB_HOST') . "\n";
echo "   - Порт: " . env('DB_PORT') . "\n";

echo "\n4. Тест кеширования:\n";
Cache::put('test_key', 'Тестовое значение', 3600);
echo "   ✓ Записано в кеш: test_key = 'Тестовое значение'\n";

$value = Cache::get('test_key');
echo "   ✓ Прочитано из кеша: " . $value . "\n";

// Проверяем, что запись попала в БД
$testRecord = DB::table('cache')->where('key', 'like', '%test_key%')->first();
if ($testRecord) {
    echo "   ✓ Запись найдена в таблице cache БД\n";
} else {
    echo "   ⚠ Запись не найдена в таблице (возможно, используется другой драйвер)\n";
}

Cache::forget('test_key');
echo "   ✓ Тестовый ключ удален\n";

echo "\n=== ПРОВЕРКА ЗАВЕРШЕНА ===\n\n";

echo "📍 Где находится кеш:\n";
echo "   - Драйвер: " . env('CACHE_DRIVER') . "\n";
echo "   - Таблица БД: laravel.cache\n";
echo "   - Путь к БД: C:\\xampp\\mysql\\data\\laravel\\\n";
echo "   - Доступ через: phpMyAdmin (http://localhost/phpmyadmin)\n";
