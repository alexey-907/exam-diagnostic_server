<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора | Диагностика</title>
    @vite(['resources/css/app.css', 'resources/js/app.ts'])
</head>
<body class="bg-slate-100 text-slate-800 font-sans min-h-screen">

<!-- Верхний навигационный бар -->
<header class="bg-slate-900 text-white shadow-md px-6 py-4 flex justify-between items-center">
    <div class="flex items-center gap-4">
        <h1 class="text-center font-black tracking-wide ">Панель управления тестированием</h1>
    </div>
    <div class="flex items-center gap-4">
        <span class="text-xs text-slate-400 bg-slate-800 px-3 py-1 rounded-full font-bold">Роль: Администратор</span>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-xs bg-rose-600 hover:bg-rose-500 text-white font-bold py-1.5 px-3 rounded-lg transition cursor-pointer">Выйти</button>
        </form>
    </div>
</header>

<main class="max-w-7xl mx-auto p-6 grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- ЛЕВАЯ КОЛОНКА: Менеджер добавления вопросов (Занимает 1 часть из 3) -->
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80 space-y-4 h-fit">
        <h2 class="text-sm font-black uppercase text-slate-400 tracking-wider">Добавить новый вопрос</h2>

        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 p-3 rounded-xl text-xs text-emerald-700 font-semibold">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-rose-50 border border-rose-200 p-3 rounded-xl text-xs text-rose-700 font-semibold">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ url('/admin/questions') }}" class="space-y-3.5">
            @csrf

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Когнитивный блок (Тест)</label>
                <select name="test_code" id="admin-test-select" class="w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-xs focus:outline-none focus:border-blue-500" onchange="toggleAdminFields()">
                    <option value="WM">Рабочая память (WM)</option>
                    <option value="LR">Логическое мышление (LR)</option>
                    <option value="AR">Абстрактное мышление (AR)</option>
                    <option value="VR">Вербальное понимание (VR)</option>
                    <option value="SP">Пространственное мышление (SP)</option>
                    <option value="ATT">Саморегуляция (SELF)</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Уникальный код</label>
                    <input type="text" name="item_code" required class="w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-xs" placeholder="WM_06, LR_09">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Сложность (1-5)</label>
                    <input type="number" name="difficulty" min="1" max="5" value="3" required class="w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-xs">
                </div>
            </div>

            <!-- Динамическое поле для ряда Рабочей Памяти (Показывается только для WM) -->
            <div id="field-wm-display" class="space-y-1">
                <label class="block text-xs font-bold text-slate-600 mb-1">Ряд чисел для демонстрации</label>
                <input type="text" name="display_data" class="w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-xs" placeholder="5-1-8-3">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Текст вопроса или инструкции</label>
                <textarea name="item_text" rows="3" required class="w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-xs resize-none" placeholder="Введите условие задачи..."></textarea>
            </div>

            <!-- Динамическое поле для вариантов ответов (Показывается для LR, VR) -->
            <div id="field-options-text" class="space-y-1 hidden">
                <label class="block text-xs font-bold text-slate-600 mb-1">Варианты ответов (Разделитель точка с запятой)</label>
                <input type="text" name="options_text" class="w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-xs" placeholder="А:Да; Б:Нет; В:Не знаю">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Правильный ответ</label>
                <input type="text" name="correct_answer" required class="w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-xs" placeholder="Например: 13 или Б или треугольник">
            </div>

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold py-3 px-4 rounded-xl shadow-md transition cursor-pointer">
                Сохранить в базу данных тестов
            </button>
        </form>
    </div>

    <!-- ПРАВАЯ КОЛОНКА: Сводные данные учеников (Занимает 2 части из 3) -->
    <!-- ПРАВАЯ КОЛОНКА: Вкладки учеников и вопросов (Занимает 2 части из 3) -->
    <div class="lg:col-span-2 space-y-4">

        <!-- Переключатели вкладок -->
        <div class="flex gap-2 bg-slate-200 p-1.5 rounded-xl w-fit">
            <button onclick="switchTab('tab-students', 'tab-questions', this)" class="tab-btn bg-white text-slate-800 text-xs font-bold px-4 py-2 rounded-lg shadow-sm transition cursor-pointer">
                👥 Результаты учеников
            </button>
            <button onclick="switchTab('tab-questions', 'tab-students', this)" class="tab-btn text-slate-600 hover:text-slate-900 text-xs font-bold px-4 py-2 rounded-lg transition cursor-pointer">
                📝 Управление вопросами ({!! count($allQuestions) !!})
            </button>
        </div>

        <!-- ВКЛАДКА А: База учеников -->
        <div id="tab-students" class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80 space-y-4">
            <h2 class="text-sm font-black uppercase text-slate-400 tracking-wider">👥 База показателей учеников</h2>
            <div class="overflow-x-auto rounded-xl border border-slate-100 shadow-inner">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                    <tr class="bg-slate-900 text-white font-bold uppercase tracking-wider">
                        <th class="p-3">Ученик / Email</th>
                        <th class="p-3">Параметры</th>
                        <th class="p-3 text-center">WM</th>
                        <th class="p-3 text-center">VM</th>
                        <th class="p-3 text-center">LR</th>
                        <th class="p-3 text-center">AR</th>
                        <th class="p-3 text-center">VR</th>
                        <th class="p-3 text-center">SP</th>
                        <th class="p-3 text-center">ATT</th>
                    </tr>
                    </thead>
                    <!-- Сюда вставляется ваш существующий <tbody> из прошлого шага со всеми forelse и раскрывающимися блоками -->
                    @include('students_table_body')
                </table>
            </div>
        </div>

        <!-- ВКЛАДКА Б: Управление вопросами (ДОБАВЛЕНО) -->
        <div id="tab-questions" class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80 space-y-4 hidden">
            <h2 class="text-sm font-black uppercase text-slate-400 tracking-wider">📝 Список вопросов в системе</h2>
            <div class="overflow-x-auto rounded-xl border border-slate-100 shadow-inner max-h-[600px] overflow-y-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                    <tr class="bg-slate-800 text-white font-bold uppercase tracking-wider sticky top-0">
                        <th class="p-3">Тест</th>
                        <th class="p-3">Код / Сложность</th>
                        <th class="p-3 w-1/3">Текст вопроса</th>
                        <th class="p-3">Ответ / Опции</th>
                        <th class="p-3 text-center">Действия</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($allQuestions as $q)
                        <tr class="hover:bg-slate-50 font-medium text-slate-700">
                            <td class="p-3 font-black text-blue-600">{{ $q->test_code }}</td>
                            <td class="p-3">
                                <span class="block font-bold text-slate-900">{{ $q->item_code }}</span>
                                <span class="block text-[10px] text-slate-400">Сложн: {{ $q->difficulty }}/5</span>
                            </td>
                            <td class="p-3 text-slate-600 font-normal">{{ Str::limit($q->item_text, 80) }}</td>
                            <td class="p-3">
                                <span class="block text-emerald-600 font-bold">🎯 {{ Str::limit(str_replace(['[',']','"'], '', $q->correct_answer_json), 25) }}</span>
                                @if($q->options_json)
                                    <span class="block text-[10px] text-slate-400">Есть варианты выбора</span>
                                @endif
                            </td>
                            <td class="p-3 text-center space-x-1.5 whitespace-nowrap">
                                <!-- Кнопка Редактировать (Передает все данные в JS-модалку) -->
                                <button type="button" onclick="openEditModal({{ json_encode($q) }})" class="bg-amber-500 hover:bg-amber-400 text-white font-bold px-2.5 py-1 rounded text-[11px] transition cursor-pointer">✏️ Изменить</button>
                                <!-- Кнопка Удалить -->
                                <a href="{{ url('/admin/questions/delete/'.$q->id) }}" onclick="return confirm('Вы уверены, что хотите полностью удалить этот вопрос из базы?')" class="bg-rose-600 hover:bg-rose-500 text-white font-bold px-2.5 py-1 rounded text-[11px] transition cursor-pointer">🗑️ Удалить</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-4 text-center text-slate-400">В базе данных пока нет добавленных вопросов.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</main>

<!-- ВСПЛЫВАЮЩЕЕ МОДАЛЬНОЕ ОКНО РЕДАКТИРОВАНИЯ (ДОБАВЛЕНО) -->
<div id="edit-modal" class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm items-center justify-center p-4 hidden z-50">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl border border-slate-200 p-6 space-y-4 relative animate-fade-in">
        <h3 class="text-sm font-black uppercase text-slate-500 tracking-wider">✏️ Редактирование вопроса</h3>

        <form id="edit-form" method="POST" action="" class="space-y-3.5 text-xs">
            @csrf
            <input type="hidden" name="test_code" id="modal-test-code">
            <input type="hidden" name="old_options_json" id="modal-old-options">

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Код вопроса</label>
                    <input type="text" name="item_code" id="modal-item-code" required class="w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Сложность (1-5)</label>
                    <input type="number" name="difficulty" id="modal-difficulty" min="1" max="5" required class="w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5">
                </div>
            </div>

            <div id="modal-wm-block" class="space-y-1 hidden">
                <label class="block text-xs font-bold text-slate-600 mb-1">Ряд чисел для показа (WM)</label>
                <input type="text" name="display_data" id="modal-display-data" class="w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Текст вопроса / задачи</label>
                <textarea name="item_text" id="modal-item-text" rows="3" required class="w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 resize-none"></textarea>
            </div>

            <div id="modal-options-block" class="space-y-1 hidden">
                <label class="block text-xs font-bold text-slate-600 mb-1">Варианты ответов (Разделитель точка с запятой)</label>
                <input type="text" name="options_text" id="modal-options-text" class="w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5" placeholder="А:Да; Б:Нет">
                <span class="text-[10px] text-slate-400 block">Оставьте пустым, если не хотите менять старую структуру вариантов</span>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Правильный ответ</label>
                <input type="text" name="correct_answer" id="modal-correct-answer" required class="w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5">
            </div>

            <div class="flex gap-2 pt-2">
                <button type="button" onclick="closeEditModal()" class="w-1/3 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold py-2.5 rounded-xl transition cursor-pointer">Отмена</button>
                <button type="submit" class="w-2/3 bg-blue-600 hover:bg-blue-500 text-white font-bold py-2.5 rounded-xl shadow-md transition cursor-pointer">Сохранить изменения</button>
            </div>
        </form>
    </div>
</div>
</body>

</main>

<!-- Скрипт для интерактивного переключения полей ввода в зависимости от теста -->
<script>
    function toggleAdminFields() {
        let select = document.getElementById('admin-test-select');
        let wmDisplay = document.getElementById('field-wm-display');
        let optionsText = document.getElementById('field-options-text');

        if (select.value === 'WM') {
            wmDisplay.classList.remove('hidden');
            optionsText.classList.add('hidden');
        } else if (select.value === 'LR' || select.value === 'VR') {
            wmDisplay.classList.add('hidden');
            optionsText.classList.remove('hidden');
        } else {
            wmDisplay.classList.add('hidden');
            optionsText.classList.add('hidden');
        }
    }
    document.addEventListener('DOMContentLoaded', toggleAdminFields);

    // Функция интерактивного переключения табов (Ученики / Вопросы)
    function switchTab(activeId, inactiveId, btn) {
        document.getElementById(activeId).classList.remove('hidden');
        document.getElementById(inactiveId).classList.add('hidden');

        document.querySelectorAll('.tab-btn').forEach(b => {
            b.classList.remove('bg-white', 'text-slate-800', 'shadow-sm');
            b.classList.add('text-slate-600');
        });
        btn.classList.add('bg-white', 'text-slate-800', 'shadow-sm');
    }

    // Функция автоматического заполнения и открытия модалки изменения вопроса
    function openEditModal(task) {
        var modal = document.getElementById('edit-modal');
        var form = document.getElementById('edit-form');

        // Динамически подставляем роут обновления по id строки
        form.action = "/admin/questions/update/" + task.id;

        document.getElementById('modal-test-code').value = task.test_code;
        document.getElementById('modal-item-code').value = task.item_code;
        document.getElementById('modal-difficulty').value = task.difficulty;
        document.getElementById('modal-item-text').value = task.item_text;
        document.getElementById('modal-old-options').value = task.options_json;

        // Очищаем правильный ответ от скобок JSON перед выводом админу
        var rawAnswer = task.correct_answer_json ? String(task.correct_answer_json).replace(/[\[\]"']/g, '') : '';
        document.getElementById('modal-correct-answer').value = rawAnswer;

        // Управление блоками полей в модалке
        var wmBlock = document.getElementById('modal-wm-block');
        var optBlock = document.getElementById('modal-options-block');

        wmBlock.classList.add('hidden');
        optBlock.classList.add('hidden');

        if (task.test_code === 'WM') {
            wmBlock.classList.remove('hidden');
            if(task.options_json) {
                var optObj = typeof task.options_json === 'string' ? JSON.parse(task.options_json) : task.options_json;
                document.getElementById('modal-display-data').value = optObj.display_data || '';
            }
        } else if (['LR', 'VR', 'ATT', 'VM'].includes(task.test_code)) {
            optBlock.classList.remove('hidden');
            document.getElementById('modal-options-text').value = ''; // Сбрасываем инпут ввода строки
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeEditModal() {
        var modal = document.getElementById('edit-modal');
        modal.classList.remove('flex');
        modal.classList.add('hidden');
    }

</script>
