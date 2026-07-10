import { type ARTask } from '../types.js';
import { state } from '../state.js';
import { switchStep} from "../navigation.js";

export const initStep8AR = () => {
    // 1. Достаем общий массив из вашей таблицы test_items
    const allDbTasks = (window as any).LaravelCognitiveTasks || [];

    // 2. Фильтруем массив, оставляя только записи для абстрактного мышления (AR)
    // ВАЖНО: Если вы занесли AR_03 в БД, мы можем отфильтровать его программно, чтобы он не попадал в тест
    const arDbTasks = allDbTasks.filter((task: any) => task.test_code === 'AR' && task.item_code !== 'AR_03');

    // 3. Маппим данные под наш TypeScript интерфейс ARTask
    const arTasks: ARTask[] = arDbTasks.map((task: any) => {
        const correctAnswerData = typeof task.correct_answer_json === 'string'
            ? JSON.parse(task.correct_answer_json)
            : task.correct_answer_json;

        const answerString = Array.isArray(correctAnswerData)
            ? correctAnswerData
            : (correctAnswerData || '');

        return {
            code: task.item_code,
            taskText: task.item_text,
            correctAnswer: String(answerString)
        };
    });

    // Защитная дефолтная заглушка (СТРОГО 7 текстовых вопросов, без спецсимволов)
    if (arTasks.length === 0) {
        arTasks.push(
            { code: 'AR_01', taskText: 'Если ▲ = 3, ● = 5, то чему равно ▲ + ● × 2 = ?', correctAnswer: '13' },
            { code: 'AR_02', taskText: 'Правило: A→2, B→4, C→6. Тогда D→?', correctAnswer: '8' },
            { code: 'AR_04', taskText: 'Если X = Y + 2, а Y = 5, чему равен X?', correctAnswer: '7' },
            { code: 'AR_05', taskText: 'Найди правило: 1→1, 2→4, 3→9, 4→16. Тогда 5→?', correctAnswer: '25' },
            { code: 'AR_06', taskText: 'Если «код» означает заменить каждую букву следующей в алфавите, то слово АБ становится?', correctAnswer: 'БВ' },
            { code: 'AR_07', taskText: 'В выражении a*b = a + b + 1. Чему равно значение 3*4?', correctAnswer: '8' },
            { code: 'AR_08', taskText: 'Если правило «сначала скобки, потом умножение», что выполняется первым в выражении (2+3)*4 ?', correctAnswer: '2+3' }
        );
    }

    let arCurrentIndex = 0;
    let arCorrectAnswersCount = 0;

    let arTimerInterval: number | null = null;
    const SECONDS_PER_TASK = 15;

    const arTaskText = document.getElementById('ar-task-text') as HTMLParagraphElement | null;
    const arUserAnswer = document.getElementById('ar-user-answer') as HTMLInputElement | null;
    const arProgressText = document.getElementById('ar-progress-text') as HTMLSpanElement | null;
    const arProgressBar = document.getElementById('ar-progress-bar') as HTMLDivElement | null;
    const arSubmitAnswerBtn = document.getElementById('ar-submit-answer-btn') as HTMLButtonElement | null;
    const arNextStepBtn = document.getElementById('ar-next-step-btn') as HTMLButtonElement | null;

    const startArTimer = () => {
        if (arTimerInterval) clearInterval(arTimerInterval);
        let timeLeft = SECONDS_PER_TASK;
        updateArTimerView(timeLeft);

        arTimerInterval = window.setInterval(() => {
            timeLeft--;
            updateArTimerView(timeLeft);

            if (timeLeft <= 0) {
                if (arTimerInterval) clearInterval(arTimerInterval);
                console.log(`⏱Время вышло на задании ${arTasks[arCurrentIndex]?.code}!`);
                handleArTimeout();
            }
        }, 1000);
    };

    const updateArTimerView = (seconds: number) => {
        if (arProgressText) {
            const color = seconds <= 10 ? 'text-rose-400 font-black animate-pulse' : 'text-amber-400';
            arProgressText.innerHTML = `Задание ${arCurrentIndex + 1} из ${arTasks.length} <span class="ml-2 font-mono ${color}">Осталось ${seconds}с</span>`;
        }
    };

    const handleArTimeout = () => {
        arCurrentIndex++;
        renderARTask();
    };
    const renderARTask = () => {
        if (arCurrentIndex >= arTasks.length) {
            finishARTest();
            return;
        }
        const currentT = arTasks[arCurrentIndex];
        const progressPercent = ((arCurrentIndex + 1) / arTasks.length) * 100;

        if (arProgressText) arProgressText.textContent = `Задание ${arCurrentIndex + 1} из ${arTasks.length}`;
        if (arProgressBar) arProgressBar.style.width = `${progressPercent}%`;
        if (arTaskText) arTaskText.textContent = currentT.taskText;
        if (arUserAnswer) {
            arUserAnswer.value = '';
            arUserAnswer.focus();
        }
        startArTimer();
    };

    const finishARTest = () => {
        // Пропорциональный расчет от 7 вопросов
        const finalScore = Math.round((arCorrectAnswersCount / arTasks.length) * 100);

        if (state.cognitive) {
            state.cognitive.arScore = finalScore;
        } else {
            state.cognitive = {
                wmScore: 0, vmScore: 0, vmImmediateWords: [], lrScore: 0,
                arScore: finalScore, vrScore: 0, spScore: 0, attScore: 0
            };
        }

        console.log('Тест абстрактного мышления AR завершен! Балл:', finalScore, state);

        const gameContainer = document.getElementById('ar-game-container');
        if (gameContainer) {
            gameContainer.innerHTML = `
                <div class="text-center space-y-2 py-4">
                    <p class="text-green-400 font-bold text-lg">🎉 Тест абстрактного мышления завершен!</p>
                    <p class="text-xs text-slate-400">Твой результат зафиксирован в системе. Нажми «Далее» для перехода к следующему блоку.</p>
                </div>
            `;
        }
        if (arNextStepBtn) {
            arNextStepBtn.removeAttribute('disabled');
            arNextStepBtn.className = "w-2/3 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-xl shadow-md transition cursor-pointer";
        }
    };

    const handleARAnswerSubmit = () => {
        if (arCurrentIndex >= arTasks.length) {
            switchStep('8', '9');
            return;
        }
        if (!arUserAnswer) return;
        if (arTimerInterval) clearInterval(arTimerInterval);

        const currentT = arTasks[arCurrentIndex];
        const cleanUserAnswer = arUserAnswer.value.trim().toUpperCase().replace(/\s+/g, '');
        const cleanCorrectAnswer = currentT.correctAnswer.toUpperCase().replace(/\s+/g, '');

        if (cleanUserAnswer === cleanCorrectAnswer) {
            arCorrectAnswersCount++;
        }
        arCurrentIndex++;
        renderARTask();
    };

    arSubmitAnswerBtn?.addEventListener('click', handleARAnswerSubmit);
    arUserAnswer?.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            handleARAnswerSubmit();
        }
    });

    document.getElementById('lr-next-question-btn')?.addEventListener('click', () => {
        setTimeout(() => {
            const step8Active = !document.getElementById('step-8')?.classList.contains('hidden');
            if (step8Active && arCurrentIndex === 0) {
                renderARTask();
            }
        }, 50);
    });
};




