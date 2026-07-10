<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Интеллектуальный выбор предметов ОГЭ/ЕГЭ</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @vite(['resources/css/app.css', 'resources/js/app.ts'])

</head>

<body class="bg-slate-50 font-sans text-slate-800">

<div id="landing-page" class="min-h-screen flex flex-col justify-between">
    <!-- Шапка -->
    <header class="bg-white shadow-sm py-4 px-6">
        <div class="max-w-5xl mx-auto flex justify-between items-center">
            <span class="text-xl font-bold text-blue-600">Навигатор ОГЭ/ЕГЭ</span>
            <span class="text-sm text-slate-500">Версия алгоритма 1.0</span>
        </div>
    </header>

    <!-- Главный контент -->
    <main class="max-w-4xl mx-auto px-6 py-12 flex-grow flex flex-col justify-center items-center text-center">
        <h1 class="text-4xl md:text-5xl font-extrabold text-slate-900 tracking-tight mb-4">
            Выбери предметы для экзаменов <span class="text-blue-600">осознанно</span>
        </h1>
        <p class="text-lg text-slate-600 max-w-2xl mb-8">
            Интеллектуальный сервис, который объединяет твои оценки, интересы и когнитивные способности, чтобы подобрать топ-3 идеальных набора предметов для поступления в вуз, колледж или профильный класс.
        </p>

        <!-- Сетка с преимуществами из ТЗ -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-3xl mb-12 text-left">
            <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-100">
                <div class="text-blue-500 font-bold mb-2">Точный расчет</div>
                <p class="text-sm text-slate-500">Идёт оценка успеваемости, отношения к предметам и цели.</p>
            </div>
            <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-100">
                <div class="text-blue-500 font-bold mb-2">Когнитивный профиль</div>
                <p class="text-sm text-slate-500">Мини-тесты на логику, память и внимание подскажут риски при подготовке.</p>
            </div>
            <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-100">
                <div class="text-blue-500 font-bold mb-2">Карта решений</div>
                <p class="text-sm text-slate-500">Вы получите не просто список, а пошаговый план действий и разбор рисков.</p>
            </div>
        </div>

        <button id="start-test-btn" class="bg-blue-600 hover:bg-blue-700 text-white text-lg font-semibold py-4 px-8 rounded-xl shadow-lg shadow-blue-500/20 transform hover:-translate-y-0.5 transition active:translate-y-0">
            Пройти диагностику
        </button>
    </main>

    <!-- Подвал -->
    <footer class="bg-slate-900 text-slate-400 py-6 text-center text-sm">
        <p>© 2026 Все права защищены. Сервис является рекомендательной системой.</p>
    </footer>
</div>
<!-- КОНЕЦ: Главная страница -->


<!-- НАЧАЛО: Модальное окно / Экран теста (изначально скрыт) -->
<div id="test-screen" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4 hidden z-50">
    <div class="bg-white w-full max-w-xl rounded-2xl shadow-2xl p-6 md:p-8 relative max-h-[90vh] overflow-y-auto">

        <!-- Кнопка закрытия -->
        <button id="close-test-btn" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 text-xl font-bold p-2">
            &times;
        </button>

        <h2 class="text-2xl font-bold text-slate-900 mb-2">Стартовый профиль</h2>
        <p class="text-sm text-slate-500 mb-6">Шаг 1 из 8: Определим стартовую точку и цели тестирования.</p>

        <form id="start-profile-form" class="space-y-5">

            <!-- ================= ШАГ 1: Стартовый профиль (Экран S1) ================= -->
            <div id="step-1" class="space-y-5">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Кто проходит тест?</label>
                    <select id="role" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition" required>
                        <option value="student">Ученик</option>
                        <option value="parent">Родитель</option>
                        <option value="teacher">Педагог / Тьютор</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">В каком ты классе?</label>
                    <select id="grade" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition" required>
                        @foreach(range(7,11) as $i)
                            <option value="{{$i}}">{{$i}} класс</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Для чего выбираешь предметы?</label>
                    <select id="examType" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition" required>
                        <option value="OGE">Для ОГЭ (9 класс)</option>
                        <option value="EGE">Для ЕГЭ (11 класс)</option>
                        <option value="EARLY">Ранняя навигация / Углубленное изучение</option>
                    </select>
                </div>

                <div class="relative">
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Регион проживания</label>

                    <!-- Добавили autocomplete="off", чтобы стандартные подсказки браузера не перекрывали наши красивое меню -->
                    <input type="text" id="region" autocomplete="off" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition" placeholder="Начните вводить регион..." required>

                    <!-- Наш выпадающий блок подсказок -->
                    <div id="region-suggestions" class="absolute left-0 right-0 mt-1 bg-white border border-slate-200 rounded-lg shadow-lg hidden z-50 max-h-48 overflow-y-auto"></div>
                </div>

                <button type="button" data-next="2" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-xl shadow-md transition mt-2">
                    Далее
                </button>
            </div>
            <!-- ================= ШАГ 2: Выбор цели (Экран S2) ================= -->
            <!-- Изначально скрыт классом 'hidden' -->
            <div id="step-2" class="space-y-5 hidden">
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Какая твоя главная цель после школы?</label>

                <div class="grid grid-cols-1 gap-3">
                    <!-- Вариант: ВУЗ -->
                    <label class="flex items-start p-3 bg-slate-50 border border-slate-200 rounded-xl cursor-pointer hover:bg-blue-50/50 transition">
                        <input type="radio" name="target_track" value="UNIVERSITY" class="mt-1 mr-3 text-blue-600 focus:ring-blue-500" checked>
                        <div>
                            <span class="block font-semibold text-slate-800">Поступление в ВУЗ</span>
                            <span class="block text-xs text-slate-500">Нацелен на высшее образование и бакалавриат/специалитет</span>
                        </div>
                    </label>

                    <!-- Вариант: Колледж -->
                    <label class="flex items-start p-3 bg-slate-50 border border-slate-200 rounded-xl cursor-pointer hover:bg-blue-50/50 transition">
                        <input type="radio" name="target_track" value="COLLEGE" class="mt-1 mr-3 text-blue-600 focus:ring-blue-500">
                        <div>
                            <span class="block font-semibold text-slate-800">Поступление в колледж / техникум</span>
                            <span class="block text-xs text-slate-500">Среднее профессиональное образование (СПО) после 9 или 11 класса</span>
                        </div>
                    </label>

                    <!-- Вариант: Профильный класс -->
                    <label class="flex items-start p-3 bg-slate-50 border border-slate-200 rounded-xl cursor-pointer hover:bg-blue-50/50 transition">
                        <input type="radio" name="target_track" value="PROFILE_CLASS" class="mt-1 mr-3 text-blue-600 focus:ring-blue-500">
                        <div>
                            <span class="block font-semibold text-slate-800">Переход в профильный класс</span>
                            <span class="block text-xs text-slate-500">Продолжение учебы в школе с углублением в ИТ, мед, инженерию и др.</span>
                        </div>
                    </label>
                </div>

                <!-- Кнопки навигации -->
                <div class="flex gap-3 mt-4">
                    <button type="button" data-back="1" class="w-1/3 bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold py-3 px-4 rounded-xl transition">
                        Назад
                    </button>
                    <button type="button" data-next="3" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-xl shadow-md transition mt-2">
                        Далее
                    </button>

                </div>
            </div>
            <!-- ================= ШАГ 3: Ввод оценок (Экран S3) ================= -->
            <!-- ================= ШАГ 3: Ввод оценок (Экран S3) ================= -->
            <div id="step-3" class="space-y-5 hidden">
                <div class="flex items-center justify-between">
                    <label class="block text-sm font-semibold text-slate-700">Укажи свои оценки по предметам</label>
                    <button type="button" id="toggle-help-btn" class="text-blue-500 hover:text-blue-600 bg-blue-50 hover:bg-blue-100 w-6 h-6 rounded-full flex items-center justify-center font-bold text-xs transition">
                        ?
                    </button>
                </div>

                <div id="help-block" class="p-3.5 bg-blue-50 border border-blue-200 rounded-xl text-xs text-blue-800 space-y-1 hidden transition-all">
                    <p class="font-semibold">💡 Как заполнять оценки:</p>
                    <p>Укажи оценку за последнюю завершенную четверть (или триместр) и итоговую оценку за прошлый учебный год.</p>
                </div>

                <!-- Контейнер для предметов (динамически обновляется) -->
                <div id="subjects-container" class="space-y-4">
                    @isset($subjects)
                        @foreach($subjects as $subject)
                            <div class="subject-row bg-slate-50 p-3 rounded-xl border border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3" data-code="{{ $subject->code }}">
                                <span class="font-medium text-slate-800 text-sm">📚 {{ $subject->title }}</span>
                                <div class="flex gap-2">
                                    <div class="w-1/2 sm:w-28">
                                        <label class="block text-[10px] uppercase tracking-wider text-slate-400 font-bold mb-0.5">Четверть</label>
                                        <select class="subject-grade w-full bg-white border border-slate-200 rounded-lg p-1.5 text-sm" data-subject="{{ $subject->code }}" data-type="quarter" required>
                                            <option value="5">5</option>
                                            <option value="4">4</option>
                                            <option value="3">3</option>
                                            <option value="2">2</option>
                                        </select>
                                    </div>
                                    <div class="w-1/2 sm:w-28">
                                        <label class="block text-[10px] uppercase tracking-wider text-slate-400 font-bold mb-0.5">За год</label>
                                        <select class="subject-grade w-full bg-white border border-slate-200 rounded-lg p-1.5 text-sm" data-subject="{{ $subject->code }}" data-type="year" required>
                                            <option value="5">5</option>
                                            <option value="4">4</option>
                                            <option value="3">3</option>
                                            <option value="2">2</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endisset
                </div>

                <div class="flex gap-3 mt-4">
                    <button type="button" data-back="2" class="w-1/3 bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold py-3 px-4 rounded-xl transition">
                        Назад
                    </button>
                    <button type="button" data-next="4" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-xl shadow-md transition mt-2">
                        Далее
                    </button>
                </div>
            </div>
            <!-- ================= ШАГ 4: Отношение к предметам (Экран S4) ================= -->
            <div id="step-4" class="space-y-5 hidden">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Отношение к школьным предметам</label>
                    <p class="text-xs text-slate-500">Оцени, насколько ты согласен с утверждениями по шкале от 1 до 5.</p>
                </div>

                <!-- Контейнер для предметов (динамически обновляется) -->
                <div id="attitude-subjects-container" class="space-y-3.5 max-h-[48vh] overflow-y-auto pr-1">
                    @isset($subjects)
                        @foreach($subjects as $subject)
                            <div class="attitude-row bg-slate-50 p-4 rounded-xl border border-slate-200/60 space-y-3" data-code="{{ $subject->code }}">
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold text-slate-800 text-sm flex items-center gap-2">📚 {{ $subject->title }}</span>
                                </div>
                                <div class="unfold-group border-t border-slate-200/60 pt-2">
                                    <button type="button" class="unfold-toggle text-xs text-blue-600 hover:text-blue-700 font-semibold flex items-center gap-1 transition focus:outline-none cursor-pointer">
                                        <span>Развернуть анкету по предмету</span> ▼
                                    </button>
                                    <div class="unfold-content hidden flex-col gap-3 mt-3 pl-2 border-l-2 border-slate-200">
                                        @isset($questions)
                                            @foreach($questions as $question)
                                                @if ($question->step === 'att')
                                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-3 rounded-lg border border-slate-100 shadow-sm">
                                                        <label class="text-xs text-slate-600 font-medium max-w-md">{{ $question->question }}</label>
                                                        <div class="flex items-center gap-1 bg-slate-50 border border-slate-200/80 p-1 rounded-xl self-start sm:self-auto">
                                                            <span class="text-xs px-1" title="Категорически против">😡</span>
                                                            @foreach(range(1, 5) as $val)
                                                                <label class="cursor-pointer">
                                                                    <input type="radio" name="q_{{ $question->id }}_{{ $subject->code }}" value="{{ $val }}" class="hidden peer" {{ $val === 3 ? 'checked' : '' }}>
                                                                    <div class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold text-slate-500 bg-white border border-slate-200 peer-checked:border-blue-500 peer-checked:bg-blue-600 peer-checked:text-white hover:border-slate-300 transition">{{ $val }}</div>
                                                                </label>
                                                            @endforeach
                                                            <span class="text-xs px-1" title="Полностью согласен">😍</span>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        @endisset
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endisset
                </div>

                <div class="flex gap-3 mt-4">
                    <button type="button" data-back="3" class="w-1/3 bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold py-3 px-4 rounded-xl transition">
                        Назад
                    </button>
                    <button type="button" data-next="5" class="w-2/3 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-xl shadow-md transition">
                        Далее
                    </button>
                </div>
            </div>

            <!-- ================= ШАГ 5: Когнитивный тест памяти (Экран S5) ================= -->
            <div id="step-5" class="space-y-5 hidden">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Когнитивная диагностика</label>
                    <p class="text-xs text-slate-500">На экране будут показаны числа на короткое время.</p>
                </div>

                <!-- Контейнер игрового движка теста -->
                <div class="bg-slate-900 text-white p-8 rounded-2xl shadow-inner flex flex-col items-center justify-center min-h-[250px] relative overflow-hidden" id="wm-game-container">

                    <!-- Состояние А: Кнопка Старта -->
                    <div id="wm-intro" class="text-center space-y-4">
                        <p class="text-sm text-slate-300 max-w-sm mx-auto">Сейчас тебе будут показаны ряды чисел или букв на 5 секунд. Твоя задача — не просто запомнить их, а выполнить с ними мысленное задание.</p>
                        <button type="button" id="start-wm-btn" class="bg-blue-600 hover:bg-blue-505 text-white font-bold py-2.5 px-6 rounded-xl transition shadow-md shadow-blue-500/20">
                            Запустить тест памяти
                        </button>
                    </div>

                    <!-- Состояние Б: Экран демонстрации задания и ряда -->
                    <div id="wm-display" class="hidden text-center space-y-4">
                        <!-- Текст задания, например: "Введи этот ряд в обратном порядке" -->
                        <p id="wm-instruction" class="text-xs uppercase tracking-wider text-blue-400 font-bold bg-blue-950/40 px-3 py-1.5 rounded-full inline-block"></p>

                        <!-- Сам ряд для запоминания -->
                        <div class="py-2">
                            <span id="wm-number" class="text-4xl md:text-5xl font-black tracking-widest text-yellow-400 select-none"></span>
                        </div>

                        <p class="text-xs text-slate-400">Запоминай ряд и условие...</p>
                    </div>

                    <!-- Состояние В: Экран ввода ответа -->
                    <div id="wm-input-zone" class="hidden text-center space-y-4 w-full max-w-sm">
                        <!-- Напоминалка задания в момент ввода -->
                        <p id="wm-reminder" class="text-xs text-slate-300 italic"></p>

                        <input type="text" id="wm-user-answer" autocomplete="off" class="w-full bg-slate-800 border border-slate-700 rounded-xl p-3 text-center text-xl font-bold tracking-widest text-white focus:outline-none focus:border-blue-500 transition" placeholder="Введите ваш ответ через дефис">

                        <button type="button" id="submit-wm-answer-btn" class="w-full bg-green-600 hover:bg-green-500 text-white font-semibold py-2 px-4 rounded-xl transition">
                            Подтвердить ответ
                        </button>
                    </div>

                    <!-- Состояние Г: Экран окончания раунда / теста -->
                    <div id="wm-finished" class="hidden text-center space-y-2">
                        <p class="text-green-400 font-bold text-lg">Тест памяти завершен!</p>
                        <p id="wm-final-status" class="text-sm font-medium text-slate-200"></p>
                        <p class="text-xs text-slate-400">Результаты зафиксированы. Нажми «Далее» для перехода к следующему блоку.</p>
                    </div>
                </div>

                <!-- Кнопки перемещения -->
                <div class="flex gap-3 mt-4">
                    <button type="button" data-back="4" class="w-1/3 bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold py-3 px-4 rounded-xl transition">
                        Назад
                    </button>
                    <!-- Кнопка Далее изначально заблокирована (disabled), пока тест не пройден полностью -->
                    <button type="button" data-next="6" id="wm-next-btn" class="w-2/3 bg-slate-300 text-slate-500 font-semibold py-3 px-4 rounded-xl cursor-not-allowed transition" disabled>
                        Далее
                    </button>
                </div>
            </div>

            <!-- ================= ШАГ 6: Вербальная память VM (Экран S6) ================= -->
            <div id="step-6" class="space-y-5 hidden">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Тест 2 из 3: Вербальная память (VM)</label>
                    <p class="text-xs text-slate-500">Оценка способности запоминать термины, определения и понятия. Тест состоит из нескольких этапов.</p>
                </div>

                <div class="bg-slate-900 text-white p-6 rounded-2xl shadow-inner min-h-[300px] flex flex-col items-center justify-center relative overflow-hidden" id="vm-verbal-container">

                    <!-- ЭТАП 0: Старт теста -->
                    <div id="vmv-intro" class="text-center space-y-4">
                        <p class="text-sm text-slate-300 max-w-sm mx-auto">Тебе будет показан список слов на 25 секунд. Постарайся запомнить их как можно лучше.</p>
                        <button type="button" id="start-vmv-btn" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-2.5 px-6 rounded-xl transition shadow-md shadow-blue-500/20">
                            Начать тест
                        </button>
                    </div>

                    <!-- ЭТАП 1: Демонстрация слов (VM_01) -->
                    <div id="vmv-show" class="hidden text-center space-y-4 w-full">
                        <p class="text-xs uppercase tracking-wider text-yellow-400 font-bold">Осталось времени: <span id="vmv-timer">25</span> сек</p>
                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 max-w-xl mx-auto py-2">
                            @foreach(['атом', 'рынок', 'закон', 'клетка', 'облако', 'договор', 'энергия', 'образ', 'эпоха', 'алгоритм'] as $word)
                                <span class="bg-slate-800 border border-slate-700 px-3 py-2 rounded-xl text-sm font-medium tracking-wide shadow-sm select-none">{{ $word }}</span>
                            @endforeach
                        </div>
                    </div>

                    <!-- ЭТАП 2: Немедленное воспроизведение (VM_02) -->
                    <div id="vmv-immediate" class="hidden text-center space-y-4 w-full max-w-md">
                        <p id="timer-immediate" class="text-center text-xs font-semibold mb-2"></p>
                        <p class="text-sm font-semibold text-slate-200">Введи все слова, которые ты запомнил (через запятую или пробел):</p>
                        <textarea id="vmv-immediate-input" rows="3" class="w-full bg-slate-800 border border-slate-700 rounded-xl p-3 text-sm text-white focus:outline-none focus:border-blue-500 transition placeholder-slate-500" placeholder="Пример: атом, рынок, закон..."></textarea>
                        <button type="button" id="submit-vmv-immediate" class="w-full bg-green-600 hover:bg-green-500 text-white font-semibold py-2 px-4 rounded-xl transition">
                            Подтвердить и перейти к узнаванию
                        </button>
                    </div>

                    <!-- ЭТАП 3: Узнавание (VM_03) -->
                    <div id="vmv-recognition" class="hidden text-center space-y-4 w-full max-w-xl">
                        <p id="timer-recognition" class="text-center text-xs font-semibold mb-2"></p>
                        <p class="text-sm font-semibold text-slate-200">Отметь галочками только те слова, которые БЫЛИ в самом первом списке:</p>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-left py-2">
                            @foreach(['атом', 'камень', 'рынок', 'формула', 'клетка', 'энергия', 'письмо'] as $recWord)
                                <label class="flex items-center p-2.5 bg-slate-800 border border-slate-700 rounded-xl cursor-pointer hover:bg-slate-750 transition">
                                    <input type="checkbox" name="vmv_rec" value="{{ $recWord }}" class="rounded text-blue-600 mr-2.5 focus:ring-blue-500 bg-slate-700 border-slate-600">
                                    <span class="text-xs font-medium text-slate-200">{{ $recWord }}</span>
                                </label>
                            @endforeach
                        </div>
                        <button type="button" id="submit-vmv-recognition" class="w-full bg-green-600 hover:bg-green-500 text-white font-semibold py-2 px-4 rounded-xl transition mt-2">
                            Подтвердить и перейти к категоризации
                        </button>
                    </div>

                    <!-- ЭТАП 4: Категоризация (VM_05) -->
                    <!-- Примечание: VM_04 (Отсроченное воспроизведение) по ТЗ идет через 5-7 минут, его мы вызовем на финальных шагах, а сейчас делаем VM_05 -->
                    <div id="vmv-categorization" class="hidden text-center space-y-4 w-full max-w-xl">
                        <p id="timer-categorization" class="text-center text-xs font-semibold mb-2"></p>
                        <p class="text-sm font-semibold text-slate-200">Распредели слова по трем смысловым группам:</p>
                        <div class="space-y-2.5 text-left max-h-[180px] overflow-y-auto pr-1">
                            @foreach(['атом', 'рынок', 'закон', 'клетка', 'облако', 'договор', 'энергия', 'образ', 'эпоха', 'алгоритм'] as $catWord)
                                <div class="flex items-center justify-between bg-slate-800 border border-slate-700 p-2 rounded-xl">
                                    <span class="text-xs font-medium text-yellow-400 pl-2">{{ $catWord }}</span>
                                    <select name="cat_{{ $catWord }}" class="bg-slate-700 border border-slate-600 text-xs rounded-lg p-1.5 text-white w-36">
                                        <option value="science">Наука</option>
                                        <option value="society">Общество</option>
                                        <option value="image">Образ</option>
                                    </select>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" id="submit-vmv-categorization" class="w-full bg-green-600 hover:bg-green-500 text-white font-semibold py-3 px-4 rounded-xl transition shadow-md">
                            Завершить тест вербальной памяти
                        </button>
                    </div>

                    <!-- ФИНАЛ: Конец теста -->
                    <div id="vmv-finished" class="hidden text-center space-y-2">
                        <p class="text-green-400 font-bold text-lg">Вербальный тест завершен!</p>
                        <p id="vmv-final-status" class="text-sm font-medium text-slate-200"></p>
                        <p class="text-xs text-slate-400">Результаты сохранены в State. Нажми «Далее».</p>
                    </div>

                </div>

                <!-- Кнопки перемещения страницы -->
                <div class="flex gap-3 mt-4">
                    <button type="button" data-back="5" class="w-1/3 bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold py-3 px-4 rounded-xl transition">
                        Назад
                    </button>
                    <button type="button" data-next="7" id="vmv-next-btn" class="w-2/3 bg-slate-300 text-slate-500 font-semibold py-3 px-4 rounded-xl cursor-not-allowed transition" disabled>
                        Далее
                    </button>
                </div>
            </div>

            <!-- ================= ШАГ 7: Логическое мышление LR (Экран S7) ================= -->
            <div id="step-7" class="space-y-5 hidden">
                <!-- Полоса прогресса теста логики -->
                <div class="space-y-1.5">
                    <div class="flex justify-between items-center text-xs font-semibold text-slate-500">
                        <span>Тест 3 из 3: Логическое мышление (LR)</span>
                        <span id="lr-progress-text">Вопрос 1 из 8</span>
                    </div>
                    <div class="w-full bg-slate-200 h-2 rounded-full overflow-hidden">
                        <div id="lr-progress-bar" class="bg-blue-600 h-full transition-all duration-300" style="width: 12.5%"></div>
                    </div>
                </div>

                <!-- Игровой терминал для вывода вопросов -->
                <div class="bg-slate-900 text-white p-6 rounded-2xl shadow-inner min-h-[220px] flex flex-col justify-between relative overflow-hidden" id="lr-game-container">

                    <!-- Контейнер текущего вопроса -->
                    <div class="space-y-4">
                        <!-- Код и текст вопроса -->
                        <p id="lr-question-text" class="text-sm font-medium text-slate-100 leading-relaxed"></p>

                        <!-- Варианты ответов (будут генерироваться через TS) -->
                        <div id="lr-options-container" class="grid grid-cols-1 gap-2.5"></div>
                    </div>

                    <!-- Нижняя панель: Пояснение (изначально скрыто) -->
                    <div id="lr-feedback" class="hidden mt-4 p-3 bg-blue-950/40 border border-blue-900/50 rounded-xl text-xs text-blue-300">
                        <span class="font-bold block mb-0.5">💡 Разбор логики:</span>
                        <p id="lr-explanation-text"></p>
                    </div>

                </div>

                <!-- Кнопки перемещения страницы -->
                <div class="flex gap-3 mt-4">
                    <button type="button" data-back="6" class="w-1/3 bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold py-3 px-4 rounded-xl transition">
                        Назад
                    </button>
                    <button type="button" id="lr-next-question-btn" class="w-2/3 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-xl shadow-md transition">
                        Далее
                    </button>
                </div>
            </div>

            <!-- ================= ШАГ 8: Абстрактно-символическое мышление AR (Экран S8) ================= -->
            <div id="step-8" class="space-y-5 hidden">
                <!-- Полоса прогресса теста абстрактного мышления -->
                <div class="space-y-1.5">
                    <div class="flex justify-between items-center text-xs font-semibold text-slate-500">
                        <span>Тест 4: Абстрактно-символическое мышление (AR)</span>
                        <span id="ar-progress-text">Задание 1 из 8</span>
                    </div>
                    <div class="w-full bg-slate-200 h-2 rounded-full overflow-hidden">
                        <div id="ar-progress-bar" class="bg-indigo-600 h-full transition-all duration-300" style="width: 12.5%"></div>
                    </div>
                </div>

                <!-- Игровой терминал для вывода заданий AR -->
                <div class="bg-slate-900 text-white p-6 rounded-2xl shadow-inner min-h-[220px] flex flex-col justify-between relative overflow-hidden" id="ar-game-container">

                    <!-- Контейнер текущего задания -->
                    <div class="space-y-4 w-full">
                        <p class="text-xs uppercase tracking-wider text-indigo-400 font-bold bg-indigo-950/40 px-3 py-1.5 rounded-full inline-block">Найди закономерность или реши задачу</p>

                        <!-- Текст задания (формулы, схемы, символы) -->
                        <p id="ar-task-text" class="text-base md:text-lg font-black tracking-wide text-yellow-400 select-none text-center py-2"></p>
                    </div>

                    <!-- Зона ввода текстового ответа -->
                    <div class="w-full max-w-xs mx-auto space-y-3">
                        <input type="text" id="ar-user-answer" autocomplete="off" class="w-full bg-slate-800 border border-slate-700 rounded-xl p-3 text-center text-xl font-bold tracking-widest text-white focus:outline-none focus:border-indigo-500 transition" placeholder="Введите ваш ответ">

                        <button type="button" id="ar-submit-answer-btn" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-2 px-4 rounded-xl transition">
                            Подтвердить ответ
                        </button>
                    </div>

                </div>

                <!-- Кнопки перемещения страницы -->
                <div class="flex gap-3 mt-4">
                    <button type="button" data-back="7" class="w-1/3 bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold py-3 px-4 rounded-xl transition">
                        Назад
                    </button>
                    <!-- Кнопка "Далее" для перехода на Шаг 9 (активируется после окончания теста) -->
                    <button type="button" data-next="9" id="ar-next-step-btn" class="w-2/3 bg-slate-300 text-slate-500 font-semibold py-3 px-4 rounded-xl cursor-not-allowed transition" disabled>
                        Далее
                    </button>
                </div>
            </div>

            <!-- ================= ШАГ 9: Вербальное понимание VR (Экран S9) ================= -->
            <!-- ================= ШАГ 9: Вербальное понимание VR (Экран S9) ================= -->
            <div id="step-9" class="space-y-5 hidden">
                <!-- Полоса прогресса -->
                <div class="space-y-1.5">
                    <div class="flex justify-between items-center text-xs font-semibold text-slate-500">
                        <span>Тест 5: Вербальное понимание (VR)</span>
                        <span id="vr-progress-text">Вопрос 1 из 5</span>
                    </div>
                    <div class="w-full bg-slate-200 h-2 rounded-full overflow-hidden">
                        <div id="vr-progress-bar" class="bg-teal-600 h-full transition-all duration-300" style="width: 20%"></div>
                    </div>
                </div>

                <!-- Контейнер теста: Текст + Вопросы -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <!-- Левая колонка: Текст для чтения -->
                    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 flex flex-col justify-center">
                        <span class="text-[10px] uppercase font-bold tracking-wider text-teal-600 mb-2">Прочитай текст:</span>
                        <p class="text-xs md:text-sm text-slate-700 leading-relaxed font-medium bg-white p-3.5 rounded-xl border border-slate-100 shadow-sm select-none">
                            «Выбор предметов для экзамена зависит не только от того, какой предмет нравится. Важно учитывать цель после школы, текущий уровень знаний, время на подготовку и требования образовательной программы. Если предмет интересен, но база слабая, его не нужно сразу исключать: сначала стоит пройти diagnostic работу и оценить, сколько времени потребуется на подготовку.»
                        </p>
                    </div>

                    <!-- Правая колонка: Игровой терминал с вопросами -->
                    <div class="bg-slate-900 text-white p-5 rounded-2xl shadow-inner min-h-[250px] flex flex-col justify-between relative overflow-hidden" id="vr-game-container">

                        <div id="vr-questions-wrapper" class="space-y-3.5 w-full flex-grow flex flex-col justify-between">
                            <div>
                                <!-- Текст вопроса -->
                                <p id="vr-question-text" class="text-xs md:text-sm font-medium text-slate-100 leading-relaxed mb-3"></p>
                                <!-- Варианты ответов -->
                                <div id="vr-options-container" class="grid grid-cols-1 gap-2"></div>
                            </div>

                            <!-- ВНУТРЕННЯЯ КНОПКА: Только для ответов на вопросы внутри теста -->
                            <button type="button" id="vr-submit-answer-btn" class="w-full bg-teal-400 hover:bg-teal-700 text-white text-xs font-semibold py-2.5 px-4 rounded-xl shadow-md transition mt-2 cursor-pointer">
                                Ответить на вопрос
                            </button>
                        </div>

                        <!-- Экран окончания теста (изначально скрыт) -->
                        <div id="vr-finished-message" class="hidden text-center space-y-2 m-auto py-4">
                            <p class="text-green-400 font-bold text-lg">Тест вербального понимания завершен!</p>
                            <p id="vr-final-score-text" class="text-xs text-slate-400"></p>
                        </div>

                    </div>

                </div>

                <!-- Кнопки перемещения страницы -->
                <div class="flex gap-3 mt-4">
                    <button type="button" data-back="8" class="w-1/3 bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold py-3 px-4 rounded-xl transition">
                        Назад
                    </button>

                    <!-- ГЛАВНАЯ КНОПКА ФОРМЫ: Изначально скрыта классом hidden, появится только в конце теста -->
                    <button type="button" data-next="10" id="vr-next-step-btn" class="w-2/3 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-xl shadow-md transition cursor-pointer hidden">
                        Далее
                    </button>
                </div>
            </div>

            <!-- ================= ШАГ 9: Вербальное понимание VR (Экран S9) ================= -->
            <!-- ================= ШАГ 10: Пространственное мышление SP (Экран S10) ================= -->
            <div id="step-10" class="space-y-5 hidden">
                <!-- Полоса прогресса -->
                <div class="space-y-1.5">
                    <div class="flex justify-between items-center text-xs font-semibold text-slate-500">
                        <span>Тест 6: Пространственное мышление (SP)</span>
                        <span id="sp-progress-text">Задание 1 из 6</span>
                    </div>
                    <div class="w-full bg-slate-200 h-2 rounded-full overflow-hidden">
                        <div id="sp-progress-bar" class="bg-purple-600 h-full transition-all duration-300" style="width: 16.6%"></div>
                    </div>
                </div>

                <!-- Игровой терминал пространственного теста -->
                <div class="bg-slate-900 text-white p-6 rounded-2xl shadow-inner min-h-[220px] flex flex-col justify-between relative overflow-hidden" id="sp-game-container">

                    <!-- Обертка для вопросов и инпута -->
                    <div id="sp-questions-wrapper" class="space-y-4 w-full flex-grow flex flex-col justify-between">
                        <div class="space-y-3">
                            <p class="text-xs uppercase tracking-wider text-purple-400 font-bold bg-purple-950/40 px-3 py-1.5 rounded-full inline-block">Мысленно представь схему или объект</p>
                            <!-- Текст вопроса -->
                            <p id="sp-question-text" class="text-sm font-medium text-slate-100 leading-relaxed"></p>
                        </div>

                        <!-- Зона ввода ответа -->
                        <div class="w-full max-w-xs mx-auto space-y-3 pt-2">
                            <input type="text" id="sp-user-answer" autocomplete="off" class="w-full bg-slate-800 border border-slate-700 rounded-xl p-3 text-center text-sm font-bold tracking-wide text-white focus:outline-none focus:border-purple-500 transition" placeholder="Введите ваш ответ">

                            <!-- Внутренняя кнопка для проверки ответа -->
                            <button type="button" id="sp-submit-answer-btn" class="w-full bg-purple-600 hover:bg-purple-500 text-white font-semibold py-2 px-4 rounded-xl transition cursor-pointer">
                                Ответить на вопрос
                            </button>
                        </div>
                    </div>

                    <!-- Экран окончания теста SP (изначально скрыт) -->
                    <div id="sp-finished-message" class="hidden text-center space-y-2 m-auto py-4">
                        <p class="text-green-400 font-bold text-lg">Тест пространственного мышления завершен!</p>
                        <p id="sp-final-score-text" class="text-xs text-slate-400"></p>
                    </div>

                </div>

                <!-- Кнопки перемещения всей формы -->
                <div class="flex gap-3 mt-4">
                    <button type="button" data-back="9" class="w-1/3 bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold py-3 px-4 rounded-xl transition">
                        Назад
                    </button>

                    <!-- Главная кнопка формы: появится только после прохождения всех 6 вопросов SP -->
                    <button type="button" data-next="11" id="sp-next-step-btn" class="w-2/3 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-xl shadow-md transition cursor-pointer hidden">
                        Далее
                    </button>
                </div>
            </div>

            <!-- ================= ШАГ 11: Внимание и саморегуляция ATT/SELF (Экран S11) ================= -->
            <div id="step-11" class="space-y-5 hidden">
                <!-- Полоса прогресса -->
                <div class="space-y-1.5">
                    <div class="flex justify-between items-center text-xs font-semibold text-slate-500">
                        <span>Тест 7: Внимание и саморегуляция (ATT)</span>
                        <span id="att-progress-text">Задание 1 из 7</span>
                    </div>
                    <div class="w-full bg-slate-200 h-2 rounded-full overflow-hidden">
                        <div id="att-progress-bar" class="bg-rose-600 h-full transition-all duration-300" style="width: 14%"></div>
                    </div>
                </div>

                <!-- Игровой терминал теста внимания -->
                <div class="bg-slate-900 text-white p-6 rounded-2xl shadow-inner min-h-[260px] flex flex-col justify-between relative overflow-hidden" id="att-game-container">

                    <!-- Контейнер для вопросов и инпутов -->
                    <div id="att-questions-wrapper" class="space-y-4 w-full flex-grow flex flex-col justify-between">
                        <div class="space-y-2">
                            <p id="att-badge-text" class="text-xs uppercase tracking-wider text-rose-400 font-bold bg-rose-950/40 px-3 py-1.5 rounded-full inline-block">Тест на концентрацию</p>
                            <!-- Текст вопроса или задания -->
                            <p id="att-question-text" class="text-sm font-medium text-slate-100 leading-relaxed"></p>
                        </div>

                        <!-- Динамическая зона ввода/выбора ответа (управляется через TS) -->
                        <div class="w-full max-w-sm mx-auto space-y-3 pt-2" id="att-interactive-zone">
                            <!-- Сюда TS будет подставлять либо input, либо шкалу 1-5, либо интерактивную сетку кликов -->
                        </div>

                        <!-- Внутренняя кнопка для подтверждения ответа -->
                        <button type="button" id="att-submit-answer-btn" class="w-full max-w-sm mx-auto bg-rose-600 hover:bg-rose-500 text-white font-semibold py-2 px-4 rounded-xl transition cursor-pointer flex justify-center">
                            Подтвердить ответ
                        </button>
                    </div>

                    <!-- Экран окончания теста ATT (изначально скрыт) -->
                    <div id="att-finished-message" class="hidden text-center space-y-2 m-auto py-4">
                        <p class="text-green-400 font-bold text-lg">Тесты внимания и саморегуляции завершены!</p>
                        <p id="att-final-score-text" class="text-xs text-slate-400">Все параметры когнитивного профиля успешно зафиксированы.</p>
                    </div>

                </div>

                <!-- Кнопки перемещения всей формы -->
                <div class="flex gap-3 mt-4">
                    <button type="button" data-back="10" class="w-1/3 bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold py-3 px-4 rounded-xl transition">
                        Назад
                    </button>

                    <!-- Главная кнопка формы: появится только после прохождения всех 7 вопросов ATT -->
                    <button type="button" data-next="12" id="att-next-step-btn" class="w-2/3 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-xl shadow-md transition cursor-pointer hidden">
                        Далее
                    </button>
                </div>
            </div>
            <!-- ================= ШАГ 12: Выбор профессионального кластера (Экран S12) ================= -->
            <div id="step-12" class="space-y-5 hidden">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Финальный шаг: Твои профессиональные интересы</label>
                    <p class="text-xs text-slate-500">Выбери одно или несколько направлений, в которых ты мечтаешь развиваться или строить карьеру в будущем. Это определит фокус твоих рекомендаций.</p>
                </div>

                <!-- Сетка профессиональных кластеров по ТЗ -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="clusters-container">

                    <!-- Кластер 1: IT -->
                    <label class="cluster-card flex items-start p-3 bg-white border border-slate-200 rounded-xl cursor-pointer hover:border-blue-400 hover:bg-blue-50/20 transition relative">
                        <input type="checkbox" name="pro_clusters" value="IT" class="cluster-checkbox mt-1 mr-3 text-blue-600 focus:ring-blue-500 rounded">
                        <div>
                            <span class="block text-sm font-semibold text-slate-800">💻 Информационные технологии (IT)</span>
                            <span class="block text-[11px] text-slate-500">Программирование, анализ данных, искусственный интеллект, веб-разработка.</span>
                        </div>
                    </label>

                    <!-- Кластер 2: Engineering -->
                    <label class="cluster-card flex items-start p-3 bg-white border border-slate-200 rounded-xl cursor-pointer hover:border-blue-400 hover:bg-blue-50/20 transition relative">
                        <input type="checkbox" name="pro_clusters" value="ENGINEERING" class="cluster-checkbox mt-1 mr-3 text-blue-600 focus:ring-blue-500 rounded">
                        <div>
                            <span class="block text-sm font-semibold text-slate-800">⚙️ Инженерия и производство</span>
                            <span class="block text-[11px] text-slate-500">Робототехника, строительство, авиация, автомобилестроение, энергетика.</span>
                        </div>
                    </label>

                    <!-- Кластер 3: Medicine -->
                    <label class="cluster-card flex items-start p-3 bg-white border border-slate-200 rounded-xl cursor-pointer hover:border-blue-400 hover:bg-blue-50/20 transition relative">
                        <input type="checkbox" name="pro_clusters" value="MEDICINE" class="cluster-checkbox mt-1 mr-3 text-blue-600 focus:ring-blue-500 rounded">
                        <div>
                            <span class="block text-sm font-semibold text-slate-800">🩺 Медицина и биохимия</span>
                            <span class="block text-[11px] text-slate-500">Лечебное дело, стоматология, фармация, генетика, ветеринария.</span>
                        </div>
                    </label>

                    <!-- Кластер 4: Economics -->
                    <label class="cluster-card flex items-start p-3 bg-white border border-slate-200 rounded-xl cursor-pointer hover:border-blue-400 hover:bg-blue-50/20 transition relative">
                        <input type="checkbox" name="pro_clusters" value="ECONOMICS" class="cluster-checkbox mt-1 mr-3 text-blue-600 focus:ring-blue-500 rounded">
                        <div>
                            <span class="block text-sm font-semibold text-slate-800">📊 Экономика и бизнес</span>
                            <span class="block text-[11px] text-slate-500">Менеджмент, маркетинг, финансы, аудит, стартапы и предпринимательство.</span>
                        </div>
                    </label>

                    <!-- Кластер 5: Humanities -->
                    <label class="cluster-card flex items-start p-3 bg-white border border-slate-200 rounded-xl cursor-pointer hover:border-blue-400 hover:bg-blue-50/20 transition relative">
                        <input type="checkbox" name="pro_clusters" value="HUMANITIES" class="cluster-checkbox mt-1 mr-3 text-blue-600 focus:ring-blue-500 rounded">
                        <div>
                            <span class="block text-sm font-semibold text-slate-800">🏛️ Гуманитарный сектор</span>
                            <span class="block text-[11px] text-slate-500">Лингвистика, международные отношения, юриспруденция, история, журналистика.</span>
                        </div>
                    </label>

                    <!-- Кластер 6: Creative -->
                    <label class="cluster-card flex items-start p-3 bg-white border border-slate-200 rounded-xl cursor-pointer hover:border-blue-400 hover:bg-blue-50/20 transition relative">
                        <input type="checkbox" name="pro_clusters" value="CREATIVE" class="cluster-checkbox mt-1 mr-3 text-blue-600 focus:ring-blue-500 rounded">
                        <div>
                            <span class="block text-sm font-semibold text-slate-800">🎨 Творчество и дизайн</span>
                            <span class="block text-[11px] text-slate-500">Архитектура, графический дизайн, режиссура, медиа, актерское мастерство.</span>
                        </div>
                    </label>

                </div>

                <!-- Кнопки перемещения всей формы -->
                <div class="flex gap-3 mt-4">
                    <button type="button" data-back="11" class="w-1/3 bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold py-3 px-4 rounded-xl transition">
                        Назад
                    </button>

                    <!-- ФИНАЛЬНАЯ КНОПКА: Отправляет весь state в Laravel -->
                    <button type="button" id="submit-all-diagnostic-btn" class="w-2/3 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3 px-4 rounded-xl shadow-md transition cursor-pointer flex justify-center items-center">
                        Получить результаты диагностики
                    </button>
                </div>
            </div>


            <!-- ================= ШАГ 13: Финал и Результаты (Экран S13) ================= -->
            <div id="step-13" class="space-y-6 hidden">
                <div class="text-center">
                    <span class="text-3xl">Ура!</span>
                    <h2 class="text-2xl font-bold text-slate-900 mt-2">Твои результаты готовы!</h2>
                    <p class="text-xs text-slate-500 mt-1">Алгоритм Scoring Engine проанализировал твои оценки, отношение к предметам и когнитивный профиль.</p>
                </div>

                <!-- БЛОК А: Топ рекомендованных наборов предметов -->
                <div class="space-y-3">
                    <h3 class="text-xs uppercase font-bold tracking-wider text-slate-400">Рекомендованные наборы предметов:</h3>

                    <!-- Сюда TypeScript будет динамически вставлять карточки топ-наборов -->
                    <div id="recommendations-output-container" class="space-y-3"></div>
                </div>

                <!-- БЛОК Б: Твой когнитивный профиль (Диагностика навыков) -->
                <div class="space-y-3 border-t border-slate-200/60 pt-4">
                    <h3 class="text-xs uppercase font-bold tracking-wider text-slate-400">Когнитивный профиль (Мышление и память):</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="cognitive-profiles-output">
                        <!-- Сюда TypeScript подставит 5 шкал: WM, VM, LR, AR, VR, ATT -->
                    </div>
                </div>

                <!-- Нижняя панель действий -->
                <div class="flex gap-3 mt-6 pt-2 border-t border-slate-100">
                    <button type="button" id="close-final-diagnostic-btn" class="w-1/3 bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold py-3 px-4 rounded-xl transition cursor-pointer text-sm">
                        Закрыть
                    </button>
                    <button type="button" onclick="window.print()" class="w-2/3 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-xl shadow-md transition cursor-pointer flex justify-center items-center gap-2 text-sm">
                        Распечатать отчет
                    </button>
                </div>
            </div>

        </form>
        <div id="result-log" class="mt-4 p-3 bg-slate-900 text-green-400 rounded-lg text-xs font-mono hidden max-h-40 overflow-y-auto"></div>
    </div>
</div>
<!-- КОНЕЦ: Модальное окно -->

</body>
</html>

<script>
    // Laravel автоматически превратит все строки из test_items в один JS-массив
    window.LaravelCognitiveTasks = {!! json_encode($cognitiveTasks ?? []) !!};
</script>

