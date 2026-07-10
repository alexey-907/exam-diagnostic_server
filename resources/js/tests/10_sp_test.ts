import { type SPQuestion } from '../types.js';
import { state } from '../state.js';

export const initStep10SP = () => {
    // 1. Достаем общий массив из вашей таблицы test_items
    const allDbTasks = (window as any).LaravelCognitiveTasks || [];

    // 2. Фильтруем массив, оставляя только записи для пространственного мышления (SP)
    const spDbTasks = allDbTasks.filter((task: any) => task.test_code === 'SP');

    // 3. Маппим данные под наш TypeScript интерфейс SPQuestion
    const spQuestions: SPQuestion[] = spDbTasks.map((task: any) => {
        // Парсим массив возможных правильных ответов/синонимов из вашего jsonb поля
        const correctAnswersData = typeof task.correct_answer_json === 'string'
            ? JSON.parse(task.correct_answer_json)
            : task.correct_answer_json || [];

        return {
            code: task.item_code,
            question: task.item_text,
            correctAnswers: Array.isArray(correctAnswersData) ? correctAnswersData : [String(correctAnswersData)]
        };
    });

    let spCurrentIndex = 0;
    let spCorrectAnswersCount = 0;

    let spTimerInterval: number | null = null;
    const SECONDS_PER_SP = 20;

    const spQuestionText = document.getElementById('sp-question-text') as HTMLParagraphElement | null;
    const spUserAnswer = document.getElementById('sp-user-answer') as HTMLInputElement | null;
    const spProgressText = document.getElementById('sp-progress-text') as HTMLSpanElement | null;
    const spProgressBarReal = document.getElementById('sp-progress-bar') as HTMLDivElement | null;

    const spQuestionsWrapper = document.getElementById('sp-questions-wrapper') as HTMLDivElement | null;
    const spFinishedMessage = document.getElementById('sp-finished-message') as HTMLDivElement | null;
    const spFinalScoreText = document.getElementById('sp-final-score-text') as HTMLParagraphElement | null;

    const spSubmitAnswerBtn = document.getElementById('sp-submit-answer-btn') as HTMLButtonElement | null;
    const spNextStepBtn = document.getElementById('sp-next-step-btn') as HTMLButtonElement | null;

    const startSpTimer = () => {
        if (spTimerInterval) clearInterval(spTimerInterval);
        let timeLeft = SECONDS_PER_SP;
        updateSpTimerView(timeLeft);

        spTimerInterval = window.setInterval(() => {
            timeLeft--;
            updateSpTimerView(timeLeft);

            if (timeLeft <= 0) {
                if (spTimerInterval) clearInterval(spTimerInterval);
                spCurrentIndex++;
                renderSPQuestion();
            }
        }, 1000);
    };

    const updateSpTimerView = (seconds: number) => {
        if (spProgressText) {
            const color = seconds <= 10 ? 'text-rose-400 font-black animate-pulse' : 'text-purple-400';
            spProgressText.innerHTML = `Задание ${spCurrentIndex + 1} из ${spQuestions.length} <span class="ml-2 font-mono ${color}">Осталось ${seconds}с</span>`;
        }
    };
    // Функция рендера текущего вопроса SP
    const renderSPQuestion = () => {
        if (spCurrentIndex >= spQuestions.length) {
            finishSPTest();
            return;
        }

        const currentQ = spQuestions[spCurrentIndex];
        const progressPercent = ((spCurrentIndex + 1) / spQuestions.length) * 100;

        if (spProgressText) spProgressText.textContent = `Задание ${spCurrentIndex + 1} из ${spQuestions.length}`;
        if (spProgressBarReal) spProgressBarReal.style.width = `${progressPercent}%`;
        if (spQuestionText) spQuestionText.innerHTML = currentQ.question;

        if (spUserAnswer) {
            spUserAnswer.value = '';
            spUserAnswer.focus();
        }
        startSpTimer();
    };

    // Функция окончания теста SP
    const finishSPTest = () => {
        const finalScore = Math.round((spCorrectAnswersCount / spQuestions.length) * 100);

        // ИСПРАВЛЕНО: Безопасное сохранение spScore с поддержкой полного интерфейса (добавлено attScore)
        if (state.cognitive) {
            state.cognitive.spScore = finalScore;
        } else {
            state.cognitive = {
                wmScore: 0,
                vmScore: 0,
                vmImmediateWords: [],
                lrScore: 0,
                arScore: 0,
                vrScore: 0,
                spScore: finalScore,
                attScore: 0
            };
        }

        console.log('Тест пространственного мышления SP успешно завершен! Балл:', finalScore, state);

        if (spQuestionsWrapper) spQuestionsWrapper.classList.add('hidden');
        if (spFinishedMessage) spFinishedMessage.classList.remove('hidden');
        if (spFinalScoreText) {
            spFinalScoreText.innerHTML = `Результат зафиксирован`;
        }

        if (spNextStepBtn) {
            spNextStepBtn.classList.remove('hidden');
        }
    }; // ИСПРАВЛЕНО: Скобка закрытия функции finishSPTest восстановлена на своем месте!

    // Кликер на кнопку ВНУТРЕННИХ ответов терминала
    const handleSPSubmit = () => {
        if (!spUserAnswer) return;
        const rawInput = spUserAnswer.value.trim().toLowerCase();

        if (!rawInput) {
            alert('Пожалуйста, введите ваш ответ в поле!');
            return;
        }
        if (spTimerInterval) clearInterval(spTimerInterval);

        const cleanUserAnswer = rawInput.replace(/[^a-zа-яё0-9]/g, '');
        const currentQ = spQuestions[spCurrentIndex];

        const isCorrect = currentQ.correctAnswers.some(ans => ans.toLowerCase().replace(/[^a-zа-яё0-9]/g, '') === cleanUserAnswer);

        if (isCorrect) {
            spCorrectAnswersCount++;
        }

        spCurrentIndex++;
        renderSPQuestion();
    };

    spSubmitAnswerBtn?.addEventListener('click', handleSPSubmit);
    spUserAnswer?.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            handleSPSubmit();
        }
    });

    document.getElementById('vr-next-step-btn')?.addEventListener('click', () => {
        spCurrentIndex = 0;
        spCorrectAnswersCount = 0;
        if (spQuestionsWrapper) spQuestionsWrapper.classList.remove('hidden');
        if (spFinishedMessage) spFinishedMessage.classList.add('hidden');
        if (spNextStepBtn) spNextStepBtn.classList.add('hidden');

        setTimeout(() => {
            renderSPQuestion();
        }, 50);
    });
};
