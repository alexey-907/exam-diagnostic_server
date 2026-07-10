// import {  } from '../types.js';
import { state } from '../state.js';
import {collectGradesFromS3} from "./3_grades.js";

// Функция сбора данных (экспортируем для финального вызова)
export const collectAttitudesFromS4 = () => {
    if (!state.subjects || state.subjects.length === 0) {
        console.warn('Внимание: state.subjects был пуст. Пересобираем оценки с Шага 3.');
        state.subjects = collectGradesFromS3();
    }

    if (!state.subjects || state.subjects.length === 0) {
        console.error('Критическая ошибка: Не удалось обнаружить предметы на странице!');
        return;
    }

    state.subjects.forEach((entry) => {
        const code = entry.subjectCode;
        const detailedAnswers: Record<string, number> = {};

        const selectedRadios = document.querySelectorAll<HTMLInputElement>(`input[name^="q_"][name$="_${code}"]:checked`);

        selectedRadios.forEach((radio) => {
            const nameAttr = radio.getAttribute('name') || '';
            const parts = nameAttr.split('_');

            if (parts.length >= 2) {
                const questionId = parts[1];
                detailedAnswers[questionId] = parseInt(radio.value, 10);
            }
        });

        entry.attitude = {
            detailedAnswers: detailedAnswers
        };
    });
};

// ВАЖНО: Добавлена функция инициализации спойлеров для экрана S4
export const initStep4Attitude = () => {
    document.querySelectorAll('.unfold-group').forEach((group) => {
        const toggleBtn = group.querySelector('.unfold-toggle') as HTMLButtonElement | null;
        const contentBlock = group.querySelector('.unfold-content') as HTMLDivElement | null;

        if (toggleBtn && contentBlock) {
            toggleBtn.addEventListener('click', () => {
                const isHidden = contentBlock.classList.toggle('hidden');
                const btnText = toggleBtn.querySelector('span');
                if (btnText) {
                    btnText.textContent = isHidden ? 'Развернуть анкету по предмету' : 'Свернуть анкету по предмету';
                }
                toggleBtn.innerHTML = isHidden ? `<span>${btnText?.textContent}</span> ▼` : `<span>${btnText?.textContent}</span> ▲`;
            });
        }
    });
};
