import { type LRQuestion } from '../types.js';
import { state } from '../state.js';
import {switchStep} from "../navigation.js";

export const initStep7LR = () => {
    // 1. Достаем общий массив из вашей таблицы test_items
    const allDbTasks = (window as any).LaravelCognitiveTasks || [];

    // 2. Фильтруем массив, оставляя только записи для теста логического мышления (LR)
    const lrDbTasks = allDbTasks.filter((task: any) => task.test_code === 'LR');

    // 3. Маппим данные под наш TypeScript интерфейс LRQuestion
    const lrQuestions: LRQuestion[] = lrDbTasks.map((task: any) => {
        // Парсим варианты ответов из вашего jsonb поля options_json
        const options = typeof task.options_json === 'string'
            ? JSON.parse(task.options_json)
            : task.options_json || [];

        // Парсим правильный ответ из вашего jsonb поля correct_answer_json
        const correctAnswerData = typeof task.correct_answer_json === 'string'
            ? JSON.parse(task.correct_answer_json)
            : task.correct_answer_json;

        // Если в JSON лежит массив, берем первый элемент, иначе саму строку
        const answerString = Array.isArray(correctAnswerData)
            ? correctAnswerData[0]
            : (correctAnswerData || '');

        return {
            code: task.item_code,
            question: task.item_text,
            options: Array.isArray(options) ? options : [],
            correctAnswer: String(answerString),
            explanation: task.explanation || ''
        };
    });

    // Защитная дефолтная заглушка на случай, если таблица в БД пока пустая
    if (lrQuestions.length === 0) {
        lrQuestions.push({
            code: 'LR_01',
            question: 'Все аналитики работают с данными. Некоторые люди, работающие с данными, программисты. Можно ли утверждать, что все аналитики программисты?',
            options: [{ key: 'А', text: 'да' }, { key: 'Б', text: 'нет' }, { key: 'В', text: 'только если они учатся в IT' }],
            correctAnswer: 'Б',
            explanation: 'Из общих посылок не следует, что все аналитики программисты.'
        });
    }

    let lrCurrentIndex = 0;
    let lrCorrectAnswersCount = 0;

    let lrTimerInterval: number | null = null;
    const SECONDS_PER_QUESTION = 15;

    const lrQuestionText = document.getElementById('lr-question-text') as HTMLParagraphElement | null;
    const lrOptionsContainer = document.getElementById('lr-options-container') as HTMLDivElement | null;
    const lrProgressText = document.getElementById('lr-progress-text') as HTMLSpanElement | null;
    const lrProgressBar = document.getElementById('lr-progress-bar') as HTMLDivElement | null;
    const lrNextQuestionBtn = document.getElementById('lr-next-question-btn') as HTMLButtonElement | null;

    const startQuestionTimer = () => {
        // Если предыдущий таймер запущен — сбрасываем его
        if (lrTimerInterval) clearInterval(lrTimerInterval);

        let timeLeft = SECONDS_PER_QUESTION;
        updateTimerView(timeLeft);

        lrTimerInterval = setInterval(() => {
            timeLeft--;
            updateTimerView(timeLeft);

            if (timeLeft <= 0) {
                if (lrTimerInterval) clearInterval(lrTimerInterval);
                // Имитируем клик/отправку ответа (передаем пустой или заведомо неверный результат)
                handleTimeoutTransition();
            }
        }, 1000);
    };

    const updateTimerView = (seconds: number) => {
        if (lrProgressText) {
            const colorClass = seconds <= 10
                ? 'bg-rose-500/20 text-rose-400 animate-pulse font-black'
                : 'bg-amber-500/20 text-amber-400';

            lrProgressText.innerHTML = `Вопрос ${lrCurrentIndex + 1} из ${lrQuestions.length} <span class="ml-3 px-2 py-0.5 rounded ${colorClass} font-mono">Осталось${seconds} сек.</span>`;
        }
    };


    const handleTimeoutTransition = () => {
        // Фиксируем ошибку (так как выбранного радио нет, handleWmAnswer / handleSPSubmit залогирует пропуск)
        // И просто двигаем индекс вперед
        lrCurrentIndex++;
        renderLRQuestion();
    };
    // Функция рендера текущего вопроса логики
    const renderLRQuestion = () => {
        if (lrCurrentIndex >= lrQuestions.length) {
            finishLRTest();
            return;
        }

        const currentQ = lrQuestions[lrCurrentIndex];
        const progressPercent = ((lrCurrentIndex + 1) / lrQuestions.length) * 100;

        if (lrProgressText) lrProgressText.textContent = `Вопрос ${lrCurrentIndex + 1} из ${lrQuestions.length}`;
        if (lrProgressBar) lrProgressBar.style.width = `${progressPercent}%`;
        if (lrQuestionText) lrQuestionText.innerHTML = currentQ.question;

        if (lrOptionsContainer) {
            lrOptionsContainer.innerHTML = '';
            currentQ.options.forEach((opt) => {
                const label = document.createElement('label');
                label.className = 'flex items-center p-3 bg-slate-800 border border-slate-700 rounded-xl cursor-pointer hover:bg-slate-750 hover:border-slate-500 transition shadow-sm';
                label.innerHTML = `
                    <input type="radio" name="lr_answer" value="${opt.key}" class="hidden peer">
                    <div class="w-5 h-5 rounded-full border border-slate-500 flex items-center justify-center mr-3 peer-checked:border-blue-500 peer-checked:bg-blue-600 transition-all text-[10px] text-white">✓</div>
                    <span class="text-xs font-medium text-slate-200">${opt.text}</span>
                `;

                label.querySelector('input')?.addEventListener('change', (e) => {
                    document.querySelectorAll('#lr-options-container label').forEach(l => l.classList.remove('border-blue-500', 'bg-slate-750'));
                    if ((e.target as HTMLInputElement).checked) {
                        label.classList.add('border-blue-500', 'bg-slate-750');
                    }
                });
                lrOptionsContainer.appendChild(label);
            });
        }

        startQuestionTimer();
    };

    // Функция завершения теста LR
    const finishLRTest = () => {
        const finalScore = Math.round((lrCorrectAnswersCount / lrQuestions.length) * 100);

        // ИСПРАВЛЕНО: Безопасное сохранение балла без перезаписи остальных шкал в ноль
        if (state.cognitive) {
            state.cognitive.lrScore = finalScore;
        } else {
            state.cognitive = {
                wmScore: 0,
                vmScore: 0,
                vmImmediateWords: [],
                lrScore: finalScore,
                arScore: 0,
                vrScore: 0,
                spScore: 0,
                attScore: 0
            };
        }
    };

    lrNextQuestionBtn?.addEventListener('click', () => {
        if (lrCurrentIndex >= lrQuestions.length) {
            switchStep('7', '8');
            return;
        }

        const selectedRadio = document.querySelector('input[name="lr_answer"]:checked') as HTMLInputElement | null;
        if (!selectedRadio) {
            alert('Пожалуйста, выбери один из вариантов ответа!');
            return;
        }

        if (lrTimerInterval) clearInterval(lrTimerInterval);

        const currentQ = lrQuestions[lrCurrentIndex];

        // Сверяем ответ строго по ТЗ
        if (selectedRadio.value === currentQ.correctAnswer) {
            lrCorrectAnswersCount++;
        }
        lrCurrentIndex++;
        renderLRQuestion();
    });

    document.getElementById('vmv-next-btn')?.addEventListener('click', () => {
        lrCurrentIndex = 0;
        lrCorrectAnswersCount = 0;
        renderLRQuestion();
    });

};

