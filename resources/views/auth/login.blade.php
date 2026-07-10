<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в систему диагностики | Экзамены</title>
    @vite(['resources/css/app.css', 'resources/js/app.ts'])
</head>
<body class="bg-blue-50 font-sans min-h-screen flex items-center justify-center p-4 relative overflow-hidden">

<div class="w-full max-w-md bg-slate-900/80 border border-slate-800/80 rounded-2xl shadow-2xl p-8 backdrop-blur-xl relative z-10 space-y-6">

    <!-- Заголовок и логотип -->
    <div class="text-center space-y-1.5">
        <span class="text-4xl block"></span>
        <h1 class="text-2xl font-black tracking-tight text-white">Авторизация</h1>
        <p class="text-xs text-slate-400 font-medium">Войдите, чтобы пройти профориентационный тест</p>
    </div>

    <!-- Вывод системных ошибок (неверный пароль или email) -->
    @if ($errors->any())
        <div class="bg-rose-950/40 border border-rose-900/50 p-3 rounded-xl text-xs text-rose-300 space-y-1">
            @foreach ($errors->all() as $error)
                <p>• {{ $error }}</p>
            @endforeach
        </div>
    @endif

    <!-- Форма отправки данных -->
    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <!-- Поле Email -->
        <div class="space-y-1.5">
            <label for="email" class="text-xs font-semibold text-slate-300">Ваш Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                   class="w-full bg-slate-800 border border-slate-700 rounded-xl p-3 text-sm text-white focus:outline-none focus:border-blue-500 transition placeholder-slate-500" placeholder="example@mail.ru">
        </div>

        <!-- Поле Пароль -->
        <div class="space-y-1.5">
            <div class="flex justify-between items-center">
                <label for="password" class="text-xs font-semibold text-slate-300">Пароль</label>
                @if (Route::has('password.request'))
                    <a class="text-[11px] text-blue-400 hover:underline" href="{{ route('password.request') }}">Забыл пароль?</a>
                @endif
            </div>
            <input type="password" id="password" name="password" required autocomplete="current-password"
                   class="w-full bg-slate-800 border border-slate-700 rounded-xl p-3 text-sm text-white focus:outline-none focus:border-blue-500 transition placeholder-slate-500" placeholder="••••••••">
        </div>

        <!-- Запомнить меня -->
        <label class="flex items-center cursor-pointer select-none">
            <input type="checkbox" name="remember" class="rounded bg-slate-800 border-slate-700 text-blue-600 focus:ring-blue-500 focus:ring-offset-slate-900">
            <span class="ml-2 text-xs font-medium text-slate-400">Запомнить меня на этом устройстве</span>
        </label>

        <!-- Кнопка Входа -->
        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white text-sm font-bold py-3 px-4 rounded-xl transition shadow-lg shadow-blue-500/10 cursor-pointer">
            Войти в личный кабинет
        </button>
    </form>

    <!-- Ссылка на регистрацию -->
    <div class="text-center pt-2">
        <p class="text-xs text-slate-400">Ещё нет аккаунта? <a href="{{ route('register') }}" class="text-blue-400 font-bold hover:underline">Создать аккаунт ученка</a></p>
    </div>

</div>
</body>
</html>
