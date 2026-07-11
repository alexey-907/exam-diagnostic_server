import {state} from "./state.js";
import {switchStep} from "./navigation.js";
export const initResultsModule = () => {
    const clusterCheckboxes = document.querySelectorAll<HTMLInputElement>('.cluster-checkbox');
    const submitAllBtn = document.getElementById('submit-all-diagnostic-btn') as HTMLButtonElement | null;

    // Интерактивная подсветка карточек проф. кластеров при выборе
    clusterCheckboxes.forEach((checkbox) => {
        checkbox.addEventListener('change', () => {
            const card = checkbox.closest('.cluster-card');
            if (checkbox.checked) {
                card?.classList.add('border-blue-500', 'bg-blue-50/30');
            } else {
                card?.classList.remove('border-blue-500', 'bg-blue-50/30');
            }
        });
    });

    // Главный кликер отправки и рендера
    submitAllBtn?.addEventListener('click', async () => {
        // 1. Собираем выбранные кластеры в массив
        const selectedClusters: string[] = [];
        document.querySelectorAll<HTMLInputElement>('.cluster-checkbox:checked').forEach((box) => {
            selectedClusters.push(box.value);
        });

        if (selectedClusters.length === 0) {
            alert('Пожалуйста, выбери хотя бы одно интересное тебе профессиональное направление!');
            return;
        }

        // Записываем интересы в глобальный state
        state.interests = selectedClusters;

        // Визуально блокируем кнопку на время расчетов сервера
        if (submitAllBtn) {
            submitAllBtn.disabled = true;
            submitAllBtn.innerHTML = `<em>Обработка</em>`;
        }

        try {
            console.log('ПОЛНЫЙ JSON-ПАКЕТ ДИАГНОСТИКИ ПО ТЗ ГОТОВ К ОТПРАВКЕ:', state);

            // 2. ОТПРАВЛЯЕМ СЕТЕВОЙ ЗАПРОС (Перенесено на свое законное место!)
            const response = await fetch('/api/diagnostic/save-all', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null)?.content || ''
                },
                body: JSON.stringify(state)
            });

            const result = await response.json();

            // Проверяем ошибки валидации или бэкенда Laravel
            if (!response.ok) {
                console.error('Ошибка бэкенда при обработке пакета:', result);
                alert('Произошла ошибка при расчете результатов. Подробности в консоли.');
                if (submitAllBtn) {
                    submitAllBtn.disabled = false;
                    submitAllBtn.innerHTML = 'Получить результаты диагностики';
                }
                return;
            }

            console.log('УСПЕХ! Бэкенд Laravel рассчитал данные сессии:', result);

            const downloadPdfBtn = document.getElementById('download-pdf-report-btn') as HTMLButtonElement | null;
            if (downloadPdfBtn && result.session_id) {
                // Навешиваем событие клика
                downloadPdfBtn.onclick = () => {
                    console.log(`📡 Запрос на скачивание PDF для сессии №${result.session_id}`);
                    // Перенаправляем браузер на роут скачивания файла
                    window.location.href = `/diagnostic/pdf/${result.session_id}`;
                };
            }

            const recContainer = document.getElementById('recommendations-output-container');
            if (recContainer && result.recommendations) {
                recContainer.innerHTML = ''; // Очищаем контейнер от старых данных

                result.recommendations.forEach((item: any, idx: number) => {
                    const card = document.createElement('div');
                    const isFirst = idx === 0;
                    card.className = `p-4 rounded-xl border transition shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3 ${
                        isFirst ? 'bg-blue-50/40 border-blue-200' : 'bg-white border-slate-200'
                    }`;

                    // Получаем данные из объяснения
                    const explanation = item.explanation || {};
                    const risks = explanation.risks || [];
                    const whyFits = explanation.why || [];
                    const firstActions = explanation.firstActions || [];
                    const summary = explanation.summary || 'Набор рекомендован на основе вашего профиля.';
                    // Формируем строку предметов
                    const subjectsString = Array.isArray(item.set) ? item.set.join(' + ') : item.set;

                    // Определяем цвет риска
                    const riskColor = item.riskLevel === 'low' ? 'text-green-600' :
                        item.riskLevel === 'moderate' ? 'text-yellow-600' :
                            'text-red-600';

                    card.innerHTML = `
                        <div class="space-y-3">
                            <!-- Заголовок: ранг и трек -->
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="inline-block text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-full ${
                        isFirst ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600'
                    }">
                                        #${idx + 1} ${item.track || 'Рекомендуемый набор'}
                                    </span>
                                    <span class="text-xs font-medium ${riskColor}">
                                         Риск: ${item.riskLevel || 'не определен'}
                                    </span>
                                </div>
                                <div class="text-right">
                                    <span class="text-xs text-slate-400 font-medium">Соответствие:</span>
                                    <span class="text-xl font-black ${isFirst ? 'text-blue-600' : 'text-slate-700'} ml-1">
                                        ${item.score}%
                                    </span>
                                </div>
                            </div>

                            <div class="bg-white/50 p-3 rounded-lg border border-slate-200/60">
                                <p class="text-sm font-bold text-slate-800 tracking-wide">${subjectsString}</p>
                            </div>

                            <div class="text-xs text-slate-600 bg-slate-50 p-2.5 rounded-lg border border-slate-100">
                                ${summary}
                            </div>

                            ${whyFits.length > 0 ? `
                                <div class="text-xs text-emerald-700 bg-emerald-50/60 p-2.5 rounded-lg border border-emerald-100">
                                    <span class="font-semibold">ПОЧЕМУ ПОДХОДИТ:</span>
                                    <ul class="list-disc list-inside mt-1 space-y-0.5">
                                        ${whyFits.map((reason: string) => `<li>${reason}</li>`).join('')}
                                    </ul>
                                </div>
                            ` : ''}

                            <!-- Риски -->
                            ${risks.length > 0 ? `
                                <div class="text-xs text-rose-700 bg-rose-50/60 p-2.5 rounded-lg border border-rose-100">
                                    <span class="font-semibold">РИСКИ:</span>
                                    <ul class="list-disc list-inside mt-1 space-y-0.5">
                                        ${risks.map((risk: string) => `<li>${risk}</li>`).join('')}
                                    </ul>
                                </div>
                            ` : ''}

                            ${firstActions.length > 0 ? `
                                <div class="text-xs text-blue-700 bg-blue-50/60 p-2.5 rounded-lg border border-blue-100">
                                    <span class="font-semibold">ШАГИ:</span>
                                    <ul class="list-decimal list-inside mt-1 space-y-0.5">
                                        ${firstActions.map((action: string) => `<li>${action}</li>`).join('')}
                                    </ul>
                                </div>
                            ` : ''}

                            ${explanation.disclaimer ? `
                                <div class="text-[10px] text-slate-400 italic border-t border-slate-200/60 pt-2 mt-1">
                                    ${explanation.disclaimer}
                                </div>
                            ` : ''}
                        </div>
                    `;
                    recContainer.appendChild(card);
                });
            }

            const cogContainer = document.getElementById('cognitive-profiles-output');
            if (cogContainer && state.cognitive) {
                cogContainer.innerHTML = '';
                const cogMap = [
                    { key: 'wmScore', name: 'Рабочая память (WM)' },
                    { key: 'vmScore', name: 'Вербальная память (VM)' },
                    { key: 'lrScore', name: 'Логическое мышление (LR)' },
                    { key: 'arScore', name: 'Абстрактное мышление (AR)' },
                    { key: 'vrScore', name: 'Вербальное понимание (VR)' },
                    { key: 'attScore', name: 'Саморегуляция (SELF)' }
                ];

                cogMap.forEach((test) => {
                    const score = (state.cognitive as any)[test.key] || 0;
                    let levelText = '';
                    let colorClass = '';

                    if (score >= 70) {
                        levelText = 'Сильная сторона';
                        colorClass = 'bg-emerald-500';
                    } else if (score >= 40) {
                        levelText = 'Средний уровень';
                        colorClass = 'bg-amber-500';
                    } else {
                        levelText = 'Зона риска';
                        colorClass = 'bg-rose-500';
                    }

                    const scaleItem = document.createElement('div');
                    scaleItem.className = 'bg-slate-50 p-3 rounded-xl border border-slate-100 space-y-1.5';
                    scaleItem.innerHTML = `
                        <div class="flex justify-between items-center text-xs">
                            <span class="font-semibold text-slate-700">${test.name}</span>
                            <span class="font-bold text-slate-900">${score} / 100</span>
                        </div>
                        <div class="w-full bg-slate-200 h-1.5 rounded-full overflow-hidden">
                            <div class="h-full ${colorClass} transition-all duration-500" style="width: ${score}%"></div>
                        </div>
                        <div class="flex justify-between items-center text-[10px]">
                            <span class="text-slate-400 font-medium">Статус профиля:</span>
                            <span class="font-bold ${
                        score >= 70 ? 'text-emerald-600' : score >= 40 ? 'text-amber-600' : 'text-rose-600'
                    }">${levelText}</span>
                        </div>
                    `;
                    cogContainer.appendChild(scaleItem);
                });
            }

            document.getElementById('close-final-diagnostic-btn')?.addEventListener('click', () => {
                const testScreen = document.getElementById('test-screen');
                testScreen?.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            });

            switchStep('12', '13');

        } catch (error) {
            console.error('Сетевая ошибка отправки всей сессии:', error);
            alert('Не удалось связаться с сервером. Проверьте сеть.');
            if (submitAllBtn) {
                submitAllBtn.disabled = false;
                submitAllBtn.innerHTML = 'Получить результаты диагностики';
            }
        }
    });
};
