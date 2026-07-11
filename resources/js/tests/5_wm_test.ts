import { type WMTask } from '../types.js';
import {  state } from '../state.js';

export const initStep5WM = () => {
    // 1. Достаем общий массив всех тестов из вашей таблицы test_items
    const allDbTasks = (window as any).LaravelCognitiveTasks || [];

    // 2. Фильтруем массив, оставляя только задания для теста рабочей памяти (WM)
    const wmDbTasks = allDbTasks.filter((task: any) => task.test_code === 'WM');

    // 3. Маппим данные из вашей структуры под наш интерфейс WMTask
    const wmTasks: WMTask[] = wmDbTasks.map((task: any) => {
        // Безопасно парсим options_json (если это строка, парсим, если уже объект — оставляем)
        const options = typeof task.options_json === 'string'
            ? JSON.parse(task.options_json)
            : task.options_json || {};

        // Безопасно парсим правильный ответ из вашего jsonb поля
        const correctAnswerData = typeof task.correct_answer_json === 'string'
            ? JSON.parse(task.correct_answer_json)
            : task.correct_answer_json;

        // Если в JSON лежит массив, берем первый элемент, если строка — её саму
        const answerString = Array.isArray(correctAnswerData)
            ? correctAnswerData[0]
            : (correctAnswerData || '');

        return {
            code: task.item_code,
            instruction: task.item_text,
            displayData: options.display_data || '', // Достаем ряд чисел из вашего options_json
            correctAnswer: String(answerString)
        };
    });

    let wmCurrentIndex = 0;
    let wmCorrectAnswersCount = 0;

    let wmInputTimer: number | null = null;
    const SECONDS_FOR_INPUT = 10;

    // Элементы состояний игры
    const wmIntro = document.getElementById('wm-intro') as HTMLDivElement | null;
    const wmDisplay = document.getElementById('wm-display') as HTMLDivElement | null;
    const wmInputZone = document.getElementById('wm-input-zone') as HTMLDivElement | null;
    const wmFinished = document.getElementById('wm-finished') as HTMLDivElement | null;

    // Интекст-элементы и кнопки
    const startWmBtn = document.getElementById('start-wm-btn') as HTMLButtonElement | null;
    const wmInstructionText = document.getElementById('wm-instruction') as HTMLParagraphElement | null;
    const wmReminderText = document.getElementById('wm-reminder') as HTMLParagraphElement | null;
    const wmNumberText = document.getElementById('wm-number') as HTMLSpanElement | null;
    const wmUserAnswer = document.getElementById('wm-user-answer') as HTMLInputElement | null;
    const submitWmAnswerBtn = document.getElementById('submit-wm-answer-btn') as HTMLButtonElement | null;
    const wmNextBtn = document.getElementById('wm-next-btn') as HTMLButtonElement | null;
    const wmFinalStatus = document.getElementById('wm-final-status') as HTMLParagraphElement | null;

    const startWmRound = () => {
        if (wmIntro) wmIntro.classList.add('hidden');
        if (wmInputZone) wmInputZone.classList.add('hidden');
        if (wmDisplay) wmDisplay.classList.remove('hidden');

        const currentTask = wmTasks[wmCurrentIndex];

        if (wmInstructionText) wmInstructionText.textContent = currentTask.instruction;
        if (wmNumberText) wmNumberText.textContent = currentTask.displayData;
        if (wmReminderText) wmReminderText.textContent = currentTask.instruction;

        setTimeout(() => {
            if (wmDisplay) wmDisplay.classList.add('hidden');
            if (wmInputZone) wmInputZone.classList.remove('hidden');
            if (wmUserAnswer) {
                wmUserAnswer.value = '';
                wmUserAnswer.focus();
            }
        }, 5000);
    };

    const startWmInputTimer = () => {
        let timeLeft = SECONDS_FOR_INPUT;
        updateWmTimerView(timeLeft);

        wmInputTimer = window.setInterval(() => {
            timeLeft--;
            updateWmTimerView(timeLeft);

            if (timeLeft <= 0) {
                if (wmInputTimer) clearInterval(wmInputTimer);
                console.log(`Время вышло на вводе задания ${wmTasks[wmCurrentIndex]?.code}!`);
                // Принудительно двигаем раунд вперед
                wmCurrentIndex++;
                if (wmCurrentIndex < wmTasks.length) {
                    startWmRound();
                } else {
                    finishWmTest();
                }
            }
        }, 1000);
    };

    const updateWmTimerView = (seconds: number) => {
        if (wmReminderText) {
            const currentTask = wmTasks[wmCurrentIndex];
            const color = seconds <= 5 ? 'text-rose-400 font-bold animate-pulse' : 'text-blue-400';
            wmReminderText.innerHTML = `${currentTask.instruction} <span class="ml-2 font-mono ${color}">⏳ ${seconds}с</span>`;
        }
    };

    startWmBtn?.addEventListener('click', () => {
        startWmRound();
    });

    const handleWmAnswer = () => {
        if (!wmUserAnswer) return;
        if (wmInputTimer) clearInterval(wmInputTimer); // Останавливаем таймер, ответ дан

        const currentTask = wmTasks[wmCurrentIndex];
        const userAnswer = wmUserAnswer.value.trim().toUpperCase().replace(/\s+/g, '');
        const cleanCorrectAnswer = currentTask.correctAnswer.toUpperCase().replace(/\s+/g, '');

        if (userAnswer === cleanCorrectAnswer) {
            wmCorrectAnswersCount++;
        }

        if (wmCurrentIndex < wmTasks.length - 1) {
            wmCurrentIndex++;
            startWmRound();
        } else {
            finishWmTest();
        }
    };

    // Выносим финиш в отдельный метод, чтобы вызывать его и по тайм-ауту
    const finishWmTest = () => {
        if (wmInputTimer) clearInterval(wmInputTimer);
        if (wmInputZone) wmInputZone.classList.add('hidden');
        if (wmFinished) wmFinished.classList.remove('hidden');

        const finalScore = Math.round((wmCorrectAnswersCount / wmTasks.length) * 100);

        let interpretation = '';
        if (finalScore >= 70) interpretation = 'Сильная сторона';
        else if (finalScore >= 40) interpretation = 'Средний уровень';
        else interpretation = 'Зона риска';

        if (wmFinalStatus) {
            wmFinalStatus.innerHTML = `Твой результат: <strong>${finalScore} баллов</strong> (${interpretation})<br>Правильных ответов: ${wmCorrectAnswersCount} из ${wmTasks.length}`;
        }

        if (state.cognitive) {
            state.cognitive.wmScore = finalScore;
        } else {
            state.cognitive = { wmScore: finalScore, vmScore: 0, vmImmediateWords: [], lrScore: 0, arScore: 0, vrScore: 0, spScore: 0, attScore: 0 };
        }

        if (wmNextBtn) {
            wmNextBtn.removeAttribute('disabled');
            wmNextBtn.className = "w-2/3 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-xl shadow-md transition cursor-pointer";
        }
    };

    submitWmAnswerBtn?.addEventListener('click', handleWmAnswer);
    wmUserAnswer?.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') { e.preventDefault(); handleWmAnswer(); }
    });
};
