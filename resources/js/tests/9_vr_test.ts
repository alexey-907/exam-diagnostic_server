import { type VRQuestion } from '../types.js';
import { state } from '../state.js'
export const initStep9VR = () => {
    const allDbTasks = (window as any).LaravelCognitiveTasks || [];
    const vrDbTasks = allDbTasks.filter((task: any) => task.test_code === 'VR');

    const vrQuestions: VRQuestion[] = vrDbTasks.map((task: any) => {
        const options = typeof task.options_json === 'string'
            ? JSON.parse(task.options_json)
            : task.options_json || [];
        const correctAnswerData = typeof task.correct_answer_json === 'string'
            ? JSON.parse(task.correct_answer_json)
            : task.correct_answer_json;
        const answerString = Array.isArray(correctAnswerData)
            ? correctAnswerData
            : (correctAnswerData || '');

        return {
            code: task.item_code,
            question: task.item_text,
            options: Array.isArray(options) ? options : [],
            correctAnswer: String(answerString)
        };
    });

    let vrCurrentIndex = 0;
    let vrCorrectAnswersCount = 0;
    let vrTimerInterval: number | null = null;
    const SECONDS_PER_VR = 25;

    const vrQuestionText = document.getElementById('vr-question-text') as HTMLParagraphElement | null;
    const vrOptionsContainer = document.getElementById('vr-options-container') as HTMLDivElement | null;
    const vrProgressText = document.getElementById('vr-progress-text') as HTMLSpanElement | null;
    const vrProgressBar = document.getElementById('vr-progress-bar') as HTMLDivElement | null;
    const vrQuestionsWrapper = document.getElementById('vr-questions-wrapper') as HTMLDivElement | null;
    const vrFinishedMessage = document.getElementById('vr-finished-message') as HTMLDivElement | null;
    const vrFinalScoreText = document.getElementById('vr-final-score-text') as HTMLParagraphElement | null;
    const vrSubmitAnswerBtn = document.getElementById('vr-submit-answer-btn') as HTMLButtonElement | null;
    const vrNextStepBtn = document.getElementById('vr-next-step-btn') as HTMLButtonElement | null;

    const startVrTimer = () => {
        if (vrTimerInterval) clearInterval(vrTimerInterval);
        let timeLeft = SECONDS_PER_VR;
        updateVrTimerView(timeLeft);

        vrTimerInterval = window.setInterval(() => {
            timeLeft--;
            updateVrTimerView(timeLeft);
            if (timeLeft <= 0) {
                if (vrTimerInterval) clearInterval(vrTimerInterval);
                console.log(`Время вышло на вопросе ${vrQuestions[vrCurrentIndex]?.code}!`);
                // Автоматически переходим к следующему вопросу
                vrCurrentIndex++;
                renderVRQuestion();
            }
        }, 1000);
    };

    const updateVrTimerView = (seconds: number) => {
        if (vrProgressText) {
            const color = seconds <= 10 ? 'text-rose-400 font-black animate-pulse' : 'text-teal-400';
            const total = vrQuestions.length;
            vrProgressText.innerHTML = `Вопрос ${vrCurrentIndex + 1} из ${total} <span class="ml-2 font-mono ${color}">⏱️ ${seconds}с</span>`;
        }
    };

    const renderVRQuestion = () => {
        if (vrCurrentIndex >= vrQuestions.length) {
            finishVRTest();
            return;
        }

        const currentQ = vrQuestions[vrCurrentIndex];
        const progressPercent = ((vrCurrentIndex + 1) / vrQuestions.length) * 100;

        if (vrProgressText) {
            const total = vrQuestions.length;
            vrProgressText.innerHTML = `Вопрос ${vrCurrentIndex + 1} из ${total} <span class="ml-2 font-mono text-teal-400">⏱️ ${SECONDS_PER_VR}с</span>`;
        }
        if (vrProgressBar) vrProgressBar.style.width = `${progressPercent}%`;
        if (vrQuestionText) vrQuestionText.innerHTML = currentQ.question;

        if (vrOptionsContainer) {
            vrOptionsContainer.innerHTML = '';

            currentQ.options.forEach((opt) => {
                // Создаем label-контейнер с классом vr-option для легкого поиска
                const label = document.createElement('label');
                label.className = 'vr-option flex items-center p-2.5 bg-slate-800 border border-slate-700 rounded-xl cursor-pointer hover:bg-slate-750 hover:border-slate-500 transition shadow-sm';
                label.dataset.value = opt.key;

                label.innerHTML = `
                    <input type="radio" name="vr_answer" value="${opt.key}" class="hidden peer">
                    <div class="w-4 h-4 rounded-full border border-slate-500 flex items-center justify-center mr-2.5 text-[9px] text-white flex-shrink-0">
                        <span class="hidden">✓</span>
                    </div>
                    <span class="text-xs text-slate-200"><strong class="text-teal-400 mr-1">${opt.key}.</strong> ${opt.text}</span>
                `;

                vrOptionsContainer.appendChild(label);
            });

            // 🔥 НАВЕШИВАЕМ ОБРАБОТЧИКИ ДЛЯ ВСЕХ ВАРИАНТОВ
            const vrOptions = vrOptionsContainer.querySelectorAll('.vr-option');
            vrOptions.forEach((label) => {
                label.addEventListener('click', function(this: HTMLElement) {
                    // Снимаем выделение со всех
                    const allOptions = this.parentElement?.querySelectorAll('.vr-option');
                    allOptions?.forEach((opt) => {
                        const div = opt.querySelector('div');
                        const span = div?.querySelector('span');
                        if (div) {
                            div.classList.remove('border-teal-500', 'bg-teal-600');
                            div.classList.add('border-slate-500');
                        }
                        if (span) span.classList.add('hidden');
                        opt.classList.remove('border-teal-500', 'bg-teal-600/10');
                        opt.classList.add('border-slate-700');
                    });

                    // Выделяем текущий
                    const div = this.querySelector('div');
                    const span = div?.querySelector('span');
                    if (div) {
                        div.classList.add('border-teal-500', 'bg-teal-600');
                        div.classList.remove('border-slate-500');
                    }
                    if (span) span.classList.remove('hidden');
                    this.classList.add('border-teal-500', 'bg-teal-600/10');
                    this.classList.remove('border-slate-700');

                    // Отмечаем radio
                    const radio = this.querySelector('input[type="radio"]') as HTMLInputElement;
                    if (radio) radio.checked = true;
                });
            });
        }

        // Активируем кнопку "Ответить на вопрос"
        if (vrSubmitAnswerBtn) {
            vrSubmitAnswerBtn.disabled = false;
            vrSubmitAnswerBtn.textContent = 'Ответить на вопрос';
            vrSubmitAnswerBtn.className = 'w-full bg-teal-600 hover:bg-teal-700 active:bg-teal-800 text-white text-xs font-semibold py-2.5 px-4 rounded-xl shadow-md transition mt-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed';
        }

        // Запускаем таймер
        startVrTimer();
    };

    const finishVRTest = () => {
        const finalScore = Math.round((vrCorrectAnswersCount / vrQuestions.length) * 100);

        if (state.cognitive) {
            state.cognitive.vrScore = finalScore;
        } else {
            state.cognitive = {
                wmScore: 0,
                vmScore: 0,
                vmImmediateWords: [],
                lrScore: 0,
                arScore: 0,
                vrScore: finalScore,
                spScore: 0,
                attScore: 0
            };
        }

        console.log('Тест VR успешно сохранен в State:', finalScore);

        if (vrQuestionsWrapper) vrQuestionsWrapper.classList.add('hidden');
        if (vrFinishedMessage) vrFinishedMessage.classList.remove('hidden');
        if (vrFinalScoreText) {
            vrFinalScoreText.innerHTML = `Результат зафиксирован: <strong>${vrCorrectAnswersCount} из ${vrQuestions.length}</strong> правильных ответов.`;
        }
        if (vrNextStepBtn) {
            vrNextStepBtn.classList.remove('hidden');
        }
    };


    vrSubmitAnswerBtn?.addEventListener('click', () => {
        const selectedRadio = document.querySelector('input[name="vr_answer"]:checked') as HTMLInputElement | null;
        if (!selectedRadio) {
            alert('Пожалуйста, выбери один из вариантов!');
            return;
        }

        // Визуальная обратная связь на кнопке
        if (vrSubmitAnswerBtn) {
            vrSubmitAnswerBtn.disabled = true;
            }

        if (vrTimerInterval) clearInterval(vrTimerInterval);

        const currentQ = vrQuestions[vrCurrentIndex];
        if (selectedRadio.value === currentQ.correctAnswer) {
            vrCorrectAnswersCount++;
            console.log(`✅ ${currentQ.code} — правильно!`);
        } else {
            console.log(`❌ ${currentQ.code} — неправильно. Правильный ответ: ${currentQ.correctAnswer}`);
        }

        // Небольшая задержка перед переходом к следующему вопросу
        setTimeout(() => {
            vrCurrentIndex++;
            renderVRQuestion();
        }, 400);
    });

    document.getElementById('ar-next-step-btn')?.addEventListener('click', () => {
        vrCurrentIndex = 0;
        vrCorrectAnswersCount = 0;
        if (vrQuestionsWrapper) vrQuestionsWrapper.classList.remove('hidden');
        if (vrFinishedMessage) vrFinishedMessage.classList.add('hidden');
        if (vrNextStepBtn) vrNextStepBtn.classList.add('hidden');
        // Небольшая задержка для плавного перехода
        setTimeout(() => {
            renderVRQuestion();
        }, 50);
    });
};

