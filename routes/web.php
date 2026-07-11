<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Api\DiagnosticController;
use App\Http\Controllers\DataController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }

    if (auth()->user()->role === 'admin') {
        return redirect()->route('admin.index');
    }

    return app(TestController::class)->index();
})->name('main');

// оптравка формы
Route::post('/api/sessions', 'App\Http\Controllers\DiagnosticController@startSession')->name('Diagnostic');
Route::post('/api/diagnostic/save-all', 'App\Http\Controllers\DataController@saveAll');

// регионы
Route::get('/api/regions', function (Request $request) {
    $query = mb_strtolower(trim($request->query('query', '')));

    if (mb_strlen($query) < 2) {
        return response()->json([]);
    }

    $regions = [
        "Алтайский край", "Амурская область", "Архангельская область", "Астраханская область",
        "Белгородская область", "Брянская область", "Владимирская область", "Волгоградская область",
        "Вологодская область", "Воронежская область", "Москва", "Санкт-Петербург", "Севастополь",
        "Еврейская автономная область", "Забайкальский край", "Ивановская область", "Иркутская область",
        "Кабардино-Балкарская Республика", "Калининградская область", "Калужская область", "Камчатский край",
        "Карачаево-Черкесская Республика", "Кемеровская область", "Кировская область", "Костромская область",
        "Краснодарский край", "Красноярский край", "Курганская область", "Курская область",
        "Ленинградская область", "Липецкая область", "Московская область", "Магаданская область", "Мурманская область",
        "Ненецкий автономный округ", "Нижегородская область", "Новгородская область", "Новосибирская область",
        "Омская область", "Оренбургская область", "Орловская область", "Пензенская область", "Пермский край",
        "Приморский край", "Псковская область", "Республика Адыгея", "Республика Алтай", "Республика Башкортостан",
        "Республика Бурятия", "Республика Дагестан", "Республика Ингушетия", "Республика Калмыкия",
        "Республика Карелия", "Республика Коми", "Республика Крым", "Республика Марий Эл", "Республика Мордовия",
        "Республика Саха (Якутия)", "Республика Северная Осетия - Алания", "Республика Татарстан", "Республика Тыва",
        "Республика Хакасия", "Ростовская область", "Рязанская область", "Самарская область",
        "Саратовская область", "Сахалинская область", "Свердловская область", "Смоленская область",
        "Ставропольский край", "Тамбовская область", "Тверская область", "Томская область", "Тульская область",
        "Тюменская область", "Удмуртская Республика", "Ульяновская область", "Хабаровский край",
        "Ханты-Мансийский автономный округ - Югра", "Челябинская область", "Чеченская Республика",
        "Чувашская Республика", "Чукотский автономный округ", "Ямало-Ненецкий автономный округ", "Ярославская область"
    ];

    $filtered = array_filter($regions, function ($region) use ($query) {
        return str_contains(mb_strtolower($region), $query);
    });

    return response()->json(array_values($filtered));
});

// админка
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    Route::post('/admin/questions', [AdminController::class, 'storeQuestion']);


    Route::post('/admin/questions/update/{id}', [AdminController::class, 'updateQuestion']);
    Route::get('/admin/questions/delete/{id}', [AdminController::class, 'deleteQuestion']);
});

Route::get('/diagnostic/pdf/{id}', [DataController::class, 'exportPdf'])->name('diagnostic.pdf');

// профиль
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
