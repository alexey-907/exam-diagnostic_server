<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация ученика | Система диагностики</title>
    @vite(['resources/css/app.css', 'resources/js/app.ts'])
</head>
<body class="bg-blue-50 font-sans min-h-screen flex items-center justify-center p-4 relative overflow-hidden">

<div class="w-full max-w-md bg-gray-600 border border-slate-800/80 rounded-2xl shadow-2xl p-8 backdrop-blur-xl relative z-10 space-y-5">

    <div class="text-center space-y-1.5">
        <h1 class="text-2xl font-black tracking-tight text-white">Регистрация</h1>
        <p class="text-xs text-slate-400 font-medium">Создай личный аккаунт ученика для прохождения тестов</p>
    </div>

    @if ($errors->any())
        <div class="bg-amber-600 border border-rose-900/50 p-3 rounded-xl text-xs text-rose-300 space-y-1">
            @foreach ($errors->all() as $error)
                <p>• {{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div class="space-y-1.5">
            <label for="name" class="text-xs font-semibold text-slate-300">Имя или логин ученика</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                   class="w-full bg-slate-800 border border-slate-700 rounded-xl p-3 text-sm text-white focus:outline-none focus:border-blue-500 transition placeholder-slate-500" placeholder="Александр">
        </div>

        <div class="space-y-1.5">
            <label for="email" class="text-xs font-semibold text-slate-300">Электронная почта</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                   class="w-full bg-slate-800 border border-slate-700 rounded-xl p-3 text-sm text-white focus:outline-none focus:border-blue-500 transition placeholder-slate-500" placeholder="student@mail.ru">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="space-y-1.5">
                <label for="password" class="text-xs font-semibold text-slate-300">Пароль</label>
                <input type="password" id="password" name="password" required autocomplete="new-password"
                       class="w-full bg-slate-800 border border-slate-700 rounded-xl p-3 text-sm text-white focus:outline-none focus:border-blue-500 transition placeholder-slate-500" placeholder="••••••••">
            </div>

            <div class="space-y-1.5">
                <label for="password_confirmation" class="text-xs font-semibold text-slate-300">Повтори пароль</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password"
                       class="w-full bg-slate-800 border border-slate-700 rounded-xl p-3 text-sm text-white focus:outline-none focus:border-blue-500 transition placeholder-slate-500" placeholder="••••••••">
            </div>
        </div>

        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white text-sm font-bold py-3 px-4 rounded-xl transition shadow-lg shadow-blue-500/10 cursor-pointer mt-2">
            Зарегистрироваться и войти
        </button>
    </form>

    <div class="text-center pt-1 border-t border-slate-800/60">
        <p class="text-xs text-slate-400">Уже зарегистрирован? <a href="{{ route('login') }}" class="text-blue-400 font-bold hover:underline">Войти в аккаунт</a></p>
    </div>

</div>
</body>
</html>
