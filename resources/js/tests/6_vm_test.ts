import { state } from '../state.js';

export const initStep6VM = () => {
    // 1. Достаем общий массив из вашей таблицы test_items
    const allDbTasks = (window as any).LaravelCognitiveTasks || [];

    // 2. Ищем запись, соответствующую вербальной памяти (VM)
    const vmDbTask = allDbTasks.find((task: any) => task.test_code === 'VM');

    // 3. Безопасно парсим JSON-данные из БД или берем дефолтные массивы, если в базе пусто
    let originalWords: string[] = ['атом', 'рынок', 'закон', 'клетка', 'облако', 'договор', 'энергия', 'образ', 'эпоха', 'алгоритм'];
    let validRecognitionWords: string[] = ['атом', 'рынок', 'клетка', 'энергия'];
    let correctGroups: Record<string, string> = {
        'атом': 'science', 'клетка': 'science', 'энергия': 'science', 'алгоритм': 'science',
        'рынок': 'society', 'закон': 'society', 'договор': 'society', 'эпоха': 'society',
        'облако': 'image', 'образ': 'image'
    };

    if (vmDbTask) {
        const options = typeof vmDbTask.options_json === 'string' ? JSON.parse(vmDbTask.options_json) : vmDbTask.options_json || {};
        const correctAnswers = typeof vmDbTask.correct_answer_json === 'string' ? JSON.parse(vmDbTask.correct_answer_json) : vmDbTask.correct_answer_json || [];

        if (options.original_words) originalWords = options.original_words;
        if (options.categories) correctGroups = options.categories;
        if (Array.isArray(correctAnswers)) validRecognitionWords = correctAnswers;
    }

    // Инициализация игровых переменных
    let scoreImmediate = 0;
    let scoreRecognition = 0;
    let scoreCategorization = 0;
    let vmvSavedWordsList: string[] = [];

    let vmvStageTimer: number | null = null;

    const vmvIntro = document.getElementById('vmv-intro') as HTMLDivElement | null;
    const vmvShow = document.getElementById('vmv-show') as HTMLDivElement | null;
    const vmvImmediate = document.getElementById('vmv-immediate') as HTMLDivElement | null;
    const vmvRecognition = document.getElementById('vmv-recognition') as HTMLDivElement | null;
    const vmvCategorization = document.getElementById('vmv-categorization') as HTMLDivElement | null;
    const vmvFinished = document.getElementById('vmv-finished') as HTMLDivElement | null;

    const startVmvBtn = document.getElementById('start-vmv-btn') as HTMLButtonElement | null;
    const vmvTimerText = document.getElementById('vmv-timer') as HTMLSpanElement | null;
    const vmvNextBtn = document.getElementById('vmv-next-btn') as HTMLButtonElement | null;
    const vmvFinalStatus = document.getElementById('vmv-final-status') as HTMLParagraphElement | null;

    const runStageTimer = (seconds: number, timerElementId: string, onTimeout: () => void) => {
        if (vmvStageTimer) clearInterval(vmvStageTimer);

        let timeLeft = seconds;
        const el = document.getElementById(timerElementId);
        if (el) el.innerHTML = `Оставшееся время: <span class="font-mono font-bold">${timeLeft}с</span>`;

        vmvStageTimer = window.setInterval(() => {
            timeLeft--;
            if (el) {
                const color = timeLeft <= 7 ? 'text-rose-400 font-black animate-pulse' : 'text-slate-200';
                el.innerHTML = `Оставшееся время: <span class="font-mono ${color}">${timeLeft}с</span>`;
            }

            if (timeLeft <= 0) {
                if (vmvStageTimer) clearInterval(vmvStageTimer);
                onTimeout();
            }
        }, 1000);
    };
    startVmvBtn?.addEventListener('click', () => {
        vmvIntro?.classList.add('hidden');
        vmvShow?.classList.remove('hidden');
        let timeLeft = 25; // Изменено обратно на 25 секунд по ТЗ
        if (vmvTimerText) vmvTimerText.textContent = timeLeft.toString();

        const timerInterval = setInterval(() => {
            timeLeft--;
            if (vmvTimerText) vmvTimerText.textContent = timeLeft.toString();

            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                vmvShow?.classList.add('hidden');
                vmvImmediate?.classList.remove('hidden');

                // Включаем таймер на 45 секунд для ручного ввода слов!
                // Добавьте в ваш HTML внутрь блока vmv-immediate тег <p id="timer-immediate"></p>
                runStageTimer(25, 'timer-immediate', () => {
                    document.getElementById('submit-vmv-immediate')?.click(); // Имитируем клик сдачи
                });

                const textarea = document.getElementById('vmv-immediate-input') as HTMLTextAreaElement | null;
                textarea?.focus();
            }
        }, 1000);
    });

    document.getElementById('submit-vmv-immediate')?.addEventListener('click', () => {
        if (vmvStageTimer) clearInterval(vmvStageTimer);
        const inputEl = document.getElementById('vmv-immediate-input') as HTMLTextAreaElement | null;
        if (!inputEl) return;

        const userWords = inputEl.value.toLowerCase()
            .replace(/[^a-zа-яё0-9\s]/g, ' ')
            .split(/\s+/)
            .filter(w => w.length > 0);

        let matchCount = 0;
        const savedList: string[] = [];

        userWords.forEach((word) => {
            if (originalWords.includes(word) && !savedList.includes(word)) {
                matchCount++;
                savedList.push(word);
            }
        });

        scoreImmediate = matchCount;
        vmvSavedWordsList = savedList;
        console.log('Балл за VM_02 (Немедленное воспроизведение):', scoreImmediate);

        vmvImmediate?.classList.add('hidden');
        vmvRecognition?.classList.remove('hidden');
    });

    document.getElementById('submit-vmv-recognition')?.addEventListener('click', () => {
        if (vmvStageTimer) clearInterval(vmvStageTimer);
        const checkedBoxes = document.querySelectorAll<HTMLInputElement>('input[name="vmv_rec"]:checked');
        let localScore = 0;

        checkedBoxes.forEach((box) => {
            const word = box.value;
            if (validRecognitionWords.includes(word)) {
                localScore += 0.5;
            } else {
                localScore -= 0.25;
            }
        });

        scoreRecognition = Math.max(0, localScore);
        console.log('Балл за VM_03 (Узнавание):', scoreRecognition);

        vmvRecognition?.classList.add('hidden');
        vmvCategorization?.classList.remove('hidden');
        runStageTimer(15, 'timer-recognition', () => {
            document.getElementById('submit-vmv-recognition')?.click();
        });
    });

    document.getElementById('submit-vmv-categorization')?.addEventListener('click', () => {
        if (vmvStageTimer) clearInterval(vmvStageTimer);
        let correctCatCount = 0;

        for (const word in correctGroups) {
            const selectEl = document.querySelector(`select[name="cat_${word}"]`) as HTMLSelectElement | null;
            if (selectEl && selectEl.value === correctGroups[word]) {
                correctCatCount++;
            }
        }

        scoreCategorization = (correctCatCount / 10) * 3;
        console.log('Балл за VM_05 (Категоризация):', scoreCategorization);

        const scoreDelayed = scoreImmediate;
        const raw = scoreImmediate + scoreRecognition * 0.5 + scoreDelayed * 1.2 + scoreCategorization * 0.8;
        const max_raw = 10 + 4 * 0.5 + 10 * 1.2 + 3 * 0.8;
        const finalNormalizedVerbalScore = Math.round((raw / max_raw) * 100);

        // ИСПРАВЛЕНО: Безопасное сохранение без перезаписи wmScore в ноль
        if (state.cognitive) {
            state.cognitive.vmScore = finalNormalizedVerbalScore;
            state.cognitive.vmImmediateWords = vmvSavedWordsList;
        } else {
            state.cognitive = {
                wmScore: 0,
                vmScore: finalNormalizedVerbalScore,
                vmImmediateWords: vmvSavedWordsList,
                lrScore: 0,
                arScore: 0,
                vrScore: 0,
                spScore: 0,
                attScore: 0
            };
        }

        vmvCategorization?.classList.add('hidden');
        vmvFinished?.classList.remove('hidden');

        let interpretation = '';
        if (finalNormalizedVerbalScore >= 70) interpretation = 'Сильная сторона 💪';
        else if (finalNormalizedVerbalScore >= 40) interpretation = 'Средний уровень 😐';
        else interpretation = 'Зона риска ⚠️';

        if (vmvFinalStatus) {
            vmvFinalStatus.innerHTML = `Твой результат: <strong>${finalNormalizedVerbalScore} баллов</strong> (${interpretation})`;
        }

        if (vmvNextBtn) {
            vmvNextBtn.removeAttribute('disabled');
            vmvNextBtn.className = "w-2/3 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-xl shadow-md transition";
        }

        console.log('Тест вербальной памяти успешно завершен! State обновлен:', state);
    });
};

