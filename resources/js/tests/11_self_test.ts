import { type SELFTask } from '../types.js';
import { state } from '../state.js';

export const initStep11SELF = () => {
    const allDbTasks = (window as any).LaravelCognitiveTasks || [];
    const selfDbTasks = allDbTasks.filter((task: any) => task.test_code === 'ATT');

    const attTasks: SELFTask[] = selfDbTasks.map((task: any) => {
        const options = typeof task.options_json === 'string'
            ? JSON.parse(task.options_json)
            : task.options_json || null;

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
        if (attQuestionText) attQuestionText.innerHTML = `<span class="text-rose-400 font-bold mr-1.5"></span> ${currentT.question}`;

        if (!attInteractiveZone) return;
        attInteractiveZone.innerHTML = '';

        if (attSubmitAnswerBtn) {
            attSubmitAnswerBtn.disabled = false;
            attSubmitAnswerBtn.textContent = 'Подтвердить ответ';
            attSubmitAnswerBtn.className = 'w-full max-w-sm mx-auto bg-brand-btnSubmit hover:bg-[#dca96f] active:bg-[#f19f42] text-white font-semibold py-2 px-4 rounded-xl transition cursor-pointer flex justify-center disabled:opacity-50 disabled:cursor-not-allowed';
        }

        // ============================================================
        // ТИП 1: Шкала 1-5 (SELF_01, SELF_03)
        // ============================================================
        if (currentT.type === 'scale-1-5') {
            let scaleHtml = `
                <div class="flex items-center justify-between gap-1 bg-[#FFFDF7] border border-slate-700/80 p-2 rounded-xl max-w-xs mx-auto">
                    <span class="text-[10px] text-slate-400 px-1">Почти никогда</span>
            `;
            for (let v = 1; v <= 5; v++) {
                const checked = v === 3 ? 'checked' : '';
                scaleHtml += `
                    <label class="cursor-pointer scale-option" data-value="${v}">
                        <input type="radio" name="att_scale" value="${v}" class="hidden peer" ${checked}>
                        <div class="scale-btn w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold text-[#C98D52] bg-[#FFFDF7] border border-[#C98D52] hover:border-slate-500 transition ${checked ? 'border-rose-500 bg-rose-600 text-bg-brand-btnSubmit' : ''}">
                            ${v}
                        </div>
                    </label>
                `;
            }
            scaleHtml += `
                    <span class="text-[10px] text-slate-400 px-1">Почти всегда</span>
                </div>
            `;
            attInteractiveZone.innerHTML = scaleHtml;

            // 🔥 НАВЕШИВАЕМ ОБРАБОТЧИКИ ДЛЯ SCALE
            const scaleOptions = attInteractiveZone.querySelectorAll('.scale-option');
            scaleOptions.forEach((label) => {
                label.addEventListener('click', function(this: HTMLElement) {
                    // Снимаем выделение со всех
                    const allBtns = this.parentElement?.querySelectorAll('.scale-btn');
                    allBtns?.forEach((btn) => {
                        btn.classList.remove('border-rose-500', 'bg-rose-600', 'text-white');
                        btn.classList.add('border-slate-700', 'text-slate-400');
                    });
                    // Выделяем текущий
                    const btn = this.querySelector('.scale-btn');
                    if (btn) {
                        btn.classList.add('border-rose-500', 'bg-rose-600', 'text-white');
                        btn.classList.remove('border-slate-700', 'text-slate-400');
                    }
                    // Отмечаем radio
                    const radio = this.querySelector('input[type="radio"]') as HTMLInputElement;
                    if (radio) radio.checked = true;
                });
            });
        }

            // ============================================================
            // ТИП 2: Кастомные варианты (SELF_02)
        // ============================================================
        else if (currentT.type === 'custom-options' && currentT.options) {
            let optionsHtml = `<div class="grid grid-cols-1 gap-2 text-left">`;
            currentT.options.forEach((opt) => {
                optionsHtml += `
                    <label class="custom-option flex items-center p-2.5 bg-[#FFFDF7] border border-[#C98D52] rounded-xl cursor-pointer hover:bg-[#f19f42] transition shadow-sm" data-value="${opt.val}">
                        <input type="radio" name="att_custom" value="${opt.val}" class="hidden peer">
                        <div class="w-4 h-4 rounded-full border border-slate-500 flex items-center justify-center mr-2.5 text-[9px] text-white flex-shrink-0">
                            <span class="hidden">✓</span>
                        </div>
                        <span class="text-xs text-slate-200">${opt.text}</span>
                    </label>
                `;
            });
            optionsHtml += `</div>`;
            attInteractiveZone.innerHTML = optionsHtml;

            // 🔥 НАВЕШИВАЕМ ОБРАБОТЧИКИ ДЛЯ CUSTOM-OPTIONS
            const customOptions = attInteractiveZone.querySelectorAll('.custom-option');
            customOptions.forEach((label) => {
                label.addEventListener('click', function(this: HTMLElement) {
                    // Снимаем выделение со всех
                    const allOptions = this.parentElement?.querySelectorAll('.custom-option');
                    allOptions?.forEach((opt) => {
                        const div = opt.querySelector('div');
                        const span = div?.querySelector('span');
                        if (div) {
                            div.classList.remove('border-rose-500', 'bg-rose-600');
                            div.classList.add('border-slate-500');
                        }
                        if (span) span.classList.add('hidden');
                        opt.classList.remove('border-rose-500');
                    });
                    // Выделяем текущий
                    const div = this.querySelector('div');
                    const span = div?.querySelector('span');
                    if (div) {
                        div.classList.add('border-rose-500', 'bg-rose-600');
                        div.classList.remove('border-slate-500');
                    }
                    if (span) span.classList.remove('hidden');
                    this.classList.add('border-rose-500');
                    // Отмечаем radio
                    const radio = this.querySelector('input[type="radio"]') as HTMLInputElement;
                    if (radio) radio.checked = true;
                });
            });
        }

            // ============================================================
            // ТИП 3: Выбор дней (SELF_04) - подсветка не нужна
        // ============================================================
        else if (currentT.type === 'select-days') {
            let selectHtml = `
                <div class="max-w-xs mx-auto">
                    <select id="att-days-select" class="w-full bg-[#FFFDF7] border border-[#C98D52] rounded-xl p-2.5 text-sm text-[#C98D52] focus:outline-none focus:border-[#dca96f] transition">
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

    // ============================================================
    // КЛИК ПО КНОПКЕ "ПОДТВЕРДИТЬ ОТВЕТ"
    // ============================================================
    attSubmitAnswerBtn?.addEventListener('click', () => {
        const currentT = attTasks[attCurrentIndex];

        if (currentT.type === 'scale-1-5') {
            const checkedRadio = document.querySelector('input[name="att_scale"]:checked') as HTMLInputElement | null;
            if (!checkedRadio) {
                alert('Пожалуйста, выбери значение на шкале!');
                return;
            }
            attEarnedPoints += parseInt(checkedRadio.value, 10);
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

        // Визуальная обратная связь на кнопке
        if (attSubmitAnswerBtn) {
            attSubmitAnswerBtn.disabled = true;
        }

        setTimeout(() => {
            attCurrentIndex++;
            renderATTTask();
        }, 400);
    });

    // ============================================================
    // ИНИЦИАЛИЗАЦИЯ ПРИ ПЕРЕХОДЕ С ШАГА 10
    // ============================================================
    document.getElementById('sp-next-step-btn')?.addEventListener('click', () => {
        attCurrentIndex = 0;
        attEarnedPoints = 0;
        if (attQuestionsWrapper) attQuestionsWrapper.classList.remove('hidden');
        if (attFinishedMessage) attFinishedMessage.classList.add('hidden');
        if (attNextStepBtn) attNextStepBtn.classList.add('hidden');
        setTimeout(() => {
            renderATTTask();
        }, 50);
    });
};
