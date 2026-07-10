import { WMTask } from '../types';
import { state } from '../state';


вот 7_lr_test.ts:
import { type LRQuestion } from '../types.js';
import { state } from '../state.js';
const lrQuestions: LRQuestion[] = [
    {code: 'LR_01',
        question: 'Все аналитики работают с данными. Некоторые люди, работающие с данными, программисты. Можно ли утверждать, что все аналитики программисты?',
        options: [{key: 'А', text: 'да'}, {key: 'Б', text: 'нет'}, {
            key: 'В', text: 'только если они учатся в IT'}],
        correctAnswer: 'Б',
        explanation: 'Из общих посылок не следует, что все аналитики программисты.'},
    {code: 'LR_02',
        question: 'Продолжи числовой ряд: 2, 4, 8, 16, ...',
        options: [{key: '24', text: '24'}, {key: '30', text: '30'}, {key: '32', text: '32'}, {
            key: '36', text: '36'}],
        correctAnswer: '32',
        explanation: 'Каждый следующий элемент умножается на 2.'},
    {code: 'LR_03',
        question: 'Если A > B, B > C, то какое утверждение верно?',
        options: [{key: 'A<C', text: 'A < C'}, {key: 'C>A', text: 'C > A'}, {
            key: 'A>C', text: 'A > C'}, {key: 'B>A', text: 'B > A'}],
        correctAnswer: 'A>C',
        explanation: 'Транзитивность отношения «больше».'},
    {code: 'LR_04',
        question: 'В классе 24 ученика. 1/3 занимается английским языком дополнительно. Сколько это человек?',
        options: [{key: '6', text: '6 человек'}, {key: '8', text: '8 человек'}, {
            key: '12', text: '12 человек'}, {key: '18', text: '18 человек'}],
        correctAnswer: '8',
        explanation: '24 / 3 = 8 человек.'},
    {code: 'LR_05',
        question: 'Если сегодня не понедельник, то кружок не проводится. Кружок проводится. Какой из этого вывод?',
        options: [{key: 'сегодня понедельник', text: 'сегодня понедельник'}, {
            key: 'сегодня вторник', text: 'сегодня вторник'
        }, {key: 'вывода нет', text: 'однозначного вывода нет'}],
        correctAnswer: 'сегодня понедельник',
        explanation: 'Правило контрапозиции в простой логике.'},
    {code: 'LR_06',
        question: 'У Пети больше баллов, чем у Иры, но меньше, чем у Оли. Кто набрал больше всех баллов?',
        options: [{key: 'Петя', text: 'Петя'}, {key: 'Ира', text: 'Ира'}, {
            key: 'Оля', text: 'Оля'}, {key: 'нельзя определить', text: 'нельзя определить'}],
        correctAnswer: 'Оля', explanation: 'Цепочка отношений: Оля > Петя > Ира.'},
    {code: 'LR_07',
        question: 'Найди лишнюю геометрическую фигуру среди перечисленных:',
        options: [{key: 'треугольник', text: 'треугольник'}, {key: 'квадрат', text: 'квадрат'}, {
            key: 'круг', text: 'круг'}, {key: 'прямоугольник', text: 'прямоугольник'}],
        correctAnswer: 'круг',
        explanation: 'Остальные фигуры являются многоугольниками, а круг — нет.'},
    {code: 'LR_08',
        question: 'Если предмет требует много формул и задач, какой риск выше всего при низкой рабочей памяти?',
        options: [{key: 'легко запомнить', text: 'легко запомнить формулы'}, {
            key: 'сложнее удерживать условия', text: 'сложнее удерживать условия в уме'
        }, {key: 'не влияет', text: 'низкая память никак не влияет'}],
        correctAnswer: 'сложнее удерживать условия',
        explanation: 'Прямая связь рабочей памяти и задач со сложными вычислениями в несколько шагов.'}];
export const initStep7LR = () => {
    let lrCurrentIndex = 0; // Текущий вопрос теста логики
    let lrCorrectAnswersCount = 0;
    const lrQuestionText = document.getElementById('lr-question-text') as HTMLParagraphElement | null;
    const lrOptionsContainer = document.getElementById('lr-options-container') as HTMLDivElement | null;
    const lrProgressText = document.getElementById('lr-progress-text') as HTMLSpanElement | null;
    const lrProgressBar = document.getElementById('lr-progress-bar') as HTMLDivElement | null;
    const lrNextQuestionBtn = document.getElementById('lr-next-question-btn') as HTMLButtonElement | null;
    const renderLRQuestion = () => {
        if (lrCurrentIndex >= lrQuestions.length) {
            finishLRTest();return;}
        const currentQ = lrQuestions[lrCurrentIndex];
        const progressPercent = ((lrCurrentIndex + 1) / lrQuestions.length) * 100;
        if (lrProgressText) lrProgressText.textContent = `Вопрос ${lrCurrentIndex + 1} из ${lrQuestions.length}`;
        if (lrProgressBar) lrProgressBar.style.width = `${progressPercent}%`;
        if (lrQuestionText) lrQuestionText.innerHTML = `<span class="text-blue-400 font-bold mr-1.5">${currentQ.code}:</span> ${currentQ.question}`;
        if (lrOptionsContainer) {lrOptionsContainer.innerHTML = '';
            currentQ.options.forEach((opt) => {
                const label = document.createElement('label');
                label.className = 'flex items-center p-3 bg-slate-800 border border-slate-700 rounded-xl cursor-pointer hover:bg-slate-750 hover:border-slate-500 transition shadow-sm';
                label.innerHTML = `
                    <input type="radio" name="lr_answer" value="${opt.key}" class="hidden peer">
                    <div class="w-5 h-5 rounded-full border border-slate-500 flex items-center justify-center mr-3 peer-checked:border-blue-500 peer-checked:bg-blue-600 transition-all text-[10px] text-white">✓</div>
                    <span class="text-xs font-medium text-slate-200">${opt.text}</span>`;
                label.querySelector('input')?.addEventListener('change', (e) => {
                    document.querySelectorAll('#lr-options-container label').forEach(l => l.classList.remove('border-blue-500', 'bg-slate-750'));
                    if ((e.target as HTMLInputElement).checked) {
                        label.classList.add('border-blue-500', 'bg-slate-750');}});
                lrOptionsContainer.appendChild(label);});}};
    const finishLRTest = () => {
        const finalScore = Math.round((lrCorrectAnswersCount / lrQuestions.length) * 100);
        if (state.cognitive) {
            state.cognitive.lrScore = finalScore;} else {
            state.cognitive = {wmScore: 0, vmScore: 0, vmImmediateWords: [], lrScore: finalScore};}
        console.log('Тест логического мышления LR успешно завершен! Балл:', finalScore, state);
        const step7Block = document.getElementById('step-7');
        const step8Block = document.getElementById('step-8');
        if (step7Block && step8Block) {
            step7Block.classList.add('hidden');
            step8Block.classList.remove('hidden');
            const logBlock = document.getElementById('result-log');
            if (logBlock) {logBlock.classList.remove('hidden');
                logBlock.innerHTML = `<span class="text-green-400">Диагностика полностью пройдена!<br>Логическое мышление (LR): ${finalScore} баллов. Сбор завершен.</span>`;}
            const subtitle = testScreen?.querySelector('p');
            if (subtitle) subtitle.textContent = 'Итоги диагностики';}};
    lrNextQuestionBtn?.addEventListener('click', () => {
        const selectedRadio = document.querySelector('input[name="lr_answer"]:checked') as HTMLInputElement | null;
        if (!selectedRadio) {alert('Пожалуйста, выбери один из вариантов ответа!');return;}
        const currentQ = lrQuestions[lrCurrentIndex];
        if (selectedRadio.value === currentQ.correctAnswer) {
            lrCorrectAnswersCount++;
            console.log(`Вопрос ${currentQ.code} — ВЕРНО!`);
        } else {console.log(`Вопрос ${currentQ.code} — ОШИБКА. Ответ: ${selectedRadio.value}, ожидалось: ${currentQ.correctAnswer}`);}
        lrCurrentIndex++;
        renderLRQuestion();});
    document.getElementById('vmv-next-btn')?.addEventListener('click', () => {
        lrCurrentIndex = 0;
        lrCorrectAnswersCount = 0;
        renderLRQuestion(); });};


// Защитная дефолтная заглушка на случай, если таблица в БД пока пустая
if (vrQuestions.length === 0) {
    vrQuestions.push(
        {
            code: 'VR_01',
            question: 'Какова главная мысль прочитанного текста?',
            options: [
                { key: 'А', text: 'выбирать только любимое' },
                { key: 'Б', text: 'учитывать несколько факторов при выборе' },
                { key: 'В', text: 'выбирать самый легкий предмет' }
            ],
            correctAnswer: 'Б'
        },
        {
            code: 'VR_02',
            question: 'Что порекомендовал автор делать, если предмет очень интересен, но база знаний по нему пока слабая?',
            options: [
                { key: 'А', text: 'сразу исключить его из списка' },
                { key: 'Б', text: 'пройти диагностическую работу и оценить время на подготовку' },
                { key: 'В', text: 'выбрать этот предмет наугад' }
            ],
            correctAnswer: 'Б'
        },
        {
            code: 'VR_03',
            question: 'Какие именно факторы выбора экзаменационных предметов прямо названы в тексте?',
            options: [
                { key: 'А', text: 'цель после школы, текущий уровень, время и требования программ' },
                { key: 'Б', text: 'только текущие школьные четвертные оценки' },
                { key: 'В', text: 'только мнение друзей и одноклассников' }
            ],
            correctAnswer: 'А'
        },
        {
            code: 'VR_04',
            question: 'Следует ли из текста логический вывод о том, что нелюбимый предмет всегда нельзя сдавать?',
            options: [
                { key: 'А', text: 'да, это прямо следует из текста' },
                { key: 'Б', text: 'нет, такого утверждения в тексте нет' },
                { key: 'В', text: 'в тексте об этом вообще не упоминается' }
            ],
            correctAnswer: 'Б'
        },
        {
            code: 'VR_05',
            question: 'Какой из следующих выводов по тексту является самым осторожным и обоснованным?',
            options: [
                { key: 'А', text: 'тестирование решает абсолютно всё' },
                { key: 'Б', text: 'для осознанного выбора необходим комплексный анализ факторов' },
                { key: 'В', text: 'выбор предметов никак не связан с будущей целью' }
            ],
            correctAnswer: 'Б'
        }
    );
}


// Защитная дефолтная заглушка на случай, если таблица в БД пока пустая
if (spQuestions.length === 0) {
    spQuestions.push(
        {
            code: 'SP_01',
            question: 'На карте север сверху, восток справа. Ученик идет 2 клетки на север и 1 на восток. Где он окажется относительно старта?',
            correctAnswers: ['на2клеткивышеина1правее', 'на2выше1правее', '2клеткивыше1правее', 'вышеиправее']
        },
        {
            code: 'SP_02',
            question: 'Квадрат сложили пополам по вертикали. Левая половина полностью совпала с правой. Какая это ось симметрии?',
            correctAnswers: ['вертикальная', 'вертикаль', 'осьвертикальная']
        },
        {
            code: 'SP_03',
            question: 'Если геометрический куб повернуть так, что верхняя грань стала передней, изменилась ли физическая форма куба?',
            correctAnswers: ['нет', 'неизменилась']
        },
        {
            code: 'SP_04',
            question: 'На схеме точка B находится правее A, а точка C выше B. Где расположена C относительно точки A?',
            correctAnswers: ['правееивыше', 'вышеиправее', 'направоивыше']
        },
        {
            code: 'SP_05',
            question: 'Какая геометрическая фигура обязательно получится при последовательном соединении трех точек, не лежащих на одной прямой?',
            correctAnswers: ['треугольник']
        },
        {
            code: 'SP_06',
            question: 'Если масштаб географической карты составляет 1:1000, то 1 см на этой карте соответствует какому расстоянию на реальной местности?',
            correctAnswers: ['10м', '10метров', 'десятьметров']
        }
    );
}


import { SELFTask } from '../types';
import { state } from '../state';

export const initStep11SELF = () => {
    // 1. Достаем общий массив из вашей таблицы test_items
    const allDbTasks = (window as any).LaravelCognitiveTasks || [];

    // 2. Фильтруем массив, оставляя только записи для блока саморегуляции (код теста в БД: ATT)
    const selfDbTasks = allDbTasks.filter((task: any) => task.test_code === 'ATT');

    // 3. Маппим данные под наш TypeScript интерфейс SELFTask
    const attTasks: SELFTask[] = selfDbTasks.map((task: any) => {
        // Парсим кастомные варианты ответов (для вопроса SELF_02), если они есть в базе
        const options = typeof task.options_json === 'string'
            ? JSON.parse(task.options_json)
            : task.options_json || null;

        // Автоматически определяем тип рендера в зависимости от кода задания
        let type: 'scale-1-5' | 'custom-options' | 'select-days' = 'scale-1-5';
        if (task.item_code === 'SELF_02') type = 'custom-options';
        if (task.item_code === 'SELF_04') type = 'select-days';

        return {
            code: task.item_code,
            type: type,
            badge: 'Анкета саморегуляции',
            question: task.item_text,
            options: Array.isArray(options) ? options : undefined
        };
    });

    // Защитная дефолтная заглушка на случай, если таблица в БД пока пустая
    if (attTasks.length === 0) {
        attTasks.push(
            {
                code: 'SELF_01',
                type: 'scale-1-5',
                badge: 'Анкета саморегуляции',
                question: 'Как часто ты самостоятельно садишься выполнять домашние задания или готовиться к урокам без напоминаний от родителей или учителей?'
            },
            {
                code: 'SELF_02',
                type: 'custom-options',
                badge: 'Анкета саморегуляции',
                question: 'Представь, что сложная учебная тема никак не получается с первого раза. Что ты обычно делаешь в такой ситуации?',
                options: [
                    { val: 1, text: 'Бросаю эту тему или откладываю на потом' },
                    { val: 2, text: 'Жду, пока объяснят в классе или само пройдет' },
                    { val: 4, text: 'Ищу понятное объяснение или видеоурок в интернете' },
                    { val: 5, text: 'Упорно тренируюсь и решаю задачи, пока не разберусь' }
                ]
            },
            {
                code: 'SELF_03',
                type: 'scale-1-5',
                badge: 'Анкета саморегуляции',
                question: 'Оцени свою внутреннюю готовность дисциплинированно вести регулярный план подготовки к экзаменам на протяжении как минимум 4 недель подряд:'
            },
            {
                code: 'SELF_04',
                type: 'select-days',
                badge: 'Анкета саморегуляции',
                question: 'Сколько дней в неделю (от 0 до 7) ты реально и честно готов выделять на глубокую самостоятельную работу по выбранному предмету?'
            }
        );
    }

    let attCurrentIndex = 0;
    let attEarnedPoints = 0;

    const attBadgeText = document.getElementById('att-badge-text') as HTMLParagraphElement | null;
    const attQuestionText = document.getElementById('att-question-text') as HTMLParagraphElement | null;
    const attInteractiveZone = document.getElementById('att-interactive-zone') as HTMLDivElement | null;
    const attProgressText = document.getElementById('att-progress-text') as HTMLSpanElement | null;
    const attProgressBar = document.getElementById('att-progress-bar') as HTMLDivElement | null;

    const attQuestionsWrapper = document.getElementById('att-questions-wrapper') as HTMLDivElement | null;
    const attFinishedMessage = document.getElementById('att-finished-message') as HTMLDivElement | null;
    const attSubmitAnswerBtn = document.getElementById('att-submit-answer-btn') as HTMLButtonElement | null;
    const attNextStepBtn = document.getElementById('att-next-step-btn') as HTMLButtonElement | null;

    const renderATTTask = () => {
        if (attCurrentIndex >= attTasks.length) {
            finishATTTest();
            return;
        }

        const currentT = attTasks[attCurrentIndex];
        const progressPercent = ((attCurrentIndex + 1) / attTasks.length) * 100;

        if (attProgressText) attProgressText.textContent = `Вопрос ${attCurrentIndex + 1} из ${attTasks.length}`;
        if (attProgressBar) attProgressBar.style.width = `${progressPercent}%`;

        if (attBadgeText) attBadgeText.textContent = currentT.badge;
        if (attQuestionText) attQuestionText.innerHTML = `<span class="text-rose-400 font-bold mr-1.5">${currentT.code}:</span> ${currentT.question}`;

        if (!attInteractiveZone) return;
        attInteractiveZone.innerHTML = '';

        if (currentT.type === 'scale-1-5') {
            let scaleHtml = `
                <div class="flex items-center justify-between gap-1 bg-slate-800/60 border border-slate-700/80 p-2 rounded-xl max-w-xs mx-auto">
                    <span class="text-[10px] text-slate-400 px-1">Почти никогда</span>
            `;
            for (let v = 1; v <= 5; v++) {
                scaleHtml += `
                    <label class="cursor-pointer">
                        <input type="radio" name="att_scale" value="${v}" class="hidden peer" ${v === 3 ? 'checked' : ''}>
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold text-slate-400 bg-slate-800 border border-slate-700 peer-checked:border-rose-500 peer-checked:bg-rose-600 peer-checked:text-white hover:border-slate-500 transition">${v}</div>
                    </label>
                `;
            }
            scaleHtml += `
                    <span class="text-[10px] text-slate-400 px-1">Почти всегда</span>
                </div>
            `;
            attInteractiveZone.innerHTML = scaleHtml;
        }
        else if (currentT.type === 'custom-options' && currentT.options) {
            let optionsHtml = `<div class="grid grid-cols-1 gap-2 text-left">`;
            currentT.options.forEach((opt) => {
                optionsHtml += `
                    <label class="flex items-center p-2.5 bg-slate-800 border border-slate-700 rounded-xl cursor-pointer hover:bg-slate-750 transition shadow-sm">
                        <input type="radio" name="att_custom" value="${opt.val}" class="hidden peer">
                        <div class="w-4 h-4 rounded-full border border-slate-500 flex items-center justify-center mr-2.5 peer-checked:border-rose-500 peer-checked:bg-rose-600 text-[9px] text-white">✓</div>
                        <span class="text-xs text-slate-200">${opt.text}</span>
                    </label>
                `;
            });
            optionsHtml += `</div>`;
            attInteractiveZone.innerHTML = optionsHtml;
        }
        else if (currentT.type === 'select-days') {
            let selectHtml = `
                <div class="max-w-xs mx-auto">
                    <select id="att-days-select" class="w-full bg-slate-800 border border-slate-700 rounded-xl p-2.5 text-sm text-slate-200 focus:outline-none focus:border-rose-500 transition">
            `;
            for (let d = 0; d <= 7; d++) {
                selectHtml += `<option value="${d}" ${d === 3 ? 'selected' : ''}>${d} дней в неделю</option>`;
            }
            selectHtml += `</select></div>`;
            attInteractiveZone.innerHTML = selectHtml;
        }
    };

    const finishATTTest = () => {
        const maxRawPossible = 20;
        const finalScore = Math.round((attEarnedPoints / maxRawPossible) * 100);

        // ИСПРАВЛЕНО: Полная структура когнитивного объекта без ошибок типов
        if (state.cognitive) {
            state.cognitive.attScore = finalScore;
        } else {
            state.cognitive = {
                wmScore: 0,
                vmScore: 0,
                vmImmediateWords: [],
                lrScore: 0,
                arScore: 0,
                vrScore: 0,
                spScore: 0,
                attScore: finalScore
            };
        }

        console.log('Саморегуляция (SELF_REG) полностью зафиксирована в State:', finalScore, state);

        if (attQuestionsWrapper) attQuestionsWrapper.classList.add('hidden');
        if (attFinishedMessage) attFinishedMessage.classList.remove('hidden');
        if (attNextStepBtn) attNextStepBtn.classList.remove('hidden');
    };

    attSubmitAnswerBtn?.addEventListener('click', () => {
        const currentT = attTasks[attCurrentIndex];

        if (currentT.type === 'scale-1-5') {
            const checkedRadio = document.querySelector('input[name="att_scale"]:checked') as HTMLInputElement | null;
            if (checkedRadio) attEarnedPoints += parseInt(checkedRadio.value, 10);
        }
        else if (currentT.type === 'custom-options') {
            const checkedRadio = document.querySelector('input[name="att_custom"]:checked') as HTMLInputElement | null;
            if (!checkedRadio) {
                alert('Пожалуйста, выбери один из вариантов ответа!');
                return;
            }
            attEarnedPoints += parseInt(checkedRadio.value, 10);
        }
        else if (currentT.type === 'select-days') {
            const selectEl = document.getElementById('att-days-select') as HTMLSelectElement | null;
            if (selectEl) {
                const days = parseInt(selectEl.value, 10);
                const regularityPoints = Math.round((days / 7) * 5);
                attEarnedPoints += regularityPoints;
            }
        }

        console.log(`Накопленные баллы SELF на шаге ${currentT.code}:`, attEarnedPoints);
        attCurrentIndex++;
        renderATTTask();
    });

    document.getElementById('sp-next-step-btn')?.addEventListener('click', () => {
        attCurrentIndex = 0;
        attEarnedPoints = 0;
