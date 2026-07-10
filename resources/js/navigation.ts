import { state } from './state.js';
import { type Role, type ExamType, type TargetTrack } from './types.js';
import { collectGradesFromS3 } from './inizilization/3_grades.js';
import { collectAttitudesFromS4 } from './inizilization/4_attitude.js';

const testScreen = document.getElementById('test-screen') as HTMLDivElement | null;
const startTestBtn = document.getElementById('start-test-btn') as HTMLButtonElement | null;
const closeTestBtn = document.getElementById('close-test-btn') as HTMLButtonElement | null;

startTestBtn?.addEventListener('click', () => {
    testScreen?.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
});

closeTestBtn?.addEventListener('click', () => {
    testScreen?.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
});

export const switchStep = (currentNum: string, targetNum: string) => {
    const currentBlock = document.getElementById(`step-${currentNum}`);
    const targetBlock = document.getElementById(`step-${targetNum}`);

    if (currentBlock && targetBlock) {
        currentBlock.classList.add('hidden');
        targetBlock.classList.remove('hidden');

        const subtitle = testScreen?.querySelector('p');
        if (subtitle) {
            subtitle.textContent = targetNum === '13' ? 'Результат' : `Шаг ${targetNum} из 12: Заполнение анкеты`;
        }
    }
};

export const initNavigation = () => {
    // Обрабатываем все кнопки "Назад"
    document.querySelectorAll<HTMLButtonElement>('[data-back]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const targetStep = btn.getAttribute('data-back')!;
            const currentStep = (parseInt(targetStep, 10) + 1).toString();
            switchStep(currentStep, targetStep);
        });
    });

    document.querySelectorAll<HTMLButtonElement>('[data-next]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const targetStep = btn.getAttribute('data-next')!;
            const currentStep = (parseInt(targetStep, 10) - 1).toString();

            if (currentStep === '1') {
                const regionInput = document.getElementById('region') as HTMLInputElement | null;
                if (regionInput && !regionInput.value.trim()) {
                    regionInput.reportValidity();
                    return;
                }
                const currentGrade = parseInt((document.getElementById('grade') as HTMLSelectElement).value, 10);
                const examType = (document.getElementById('examType') as HTMLSelectElement).value as ExamType;

                const now = new Date();
                const currentYear = now.getFullYear();
                const currentMonth = now.getMonth(); // 0 - Январь, 8 - Сентябрь...

                const targetGrade = examType === 'OGE' ? 9 : 11;

                let gradesLeft = targetGrade - currentGrade;

                if (currentMonth >= 8) {
                    if (gradesLeft === 0) gradesLeft = 1;
                } else {
                     if (gradesLeft < 0) gradesLeft = 0;
                }

                const calculatedTargetYear = currentYear + gradesLeft;

                state.region = regionInput?.value || null;
                state.role = (document.getElementById('role') as HTMLSelectElement).value as Role;
                state.grade = currentGrade;
                state.examType = examType;
                state.targetYear = calculatedTargetYear;
            }

            if (currentStep === '2') {
                const selectedTrack = document.querySelector('input[name="target_track"]:checked') as HTMLInputElement | null;
                state.targetTrack = selectedTrack ? (selectedTrack.value as TargetTrack) : null;
                console.log('Данные S1 и S2 сохранены в State:', state);
            }

            if (currentStep === '3') {
                state.subjects = collectGradesFromS3();
                console.log('Собраны оценки с экрана S3 в state:', state.subjects);
            }

            if (currentStep === '4') {
                collectAttitudesFromS4();
                console.log('Собраны отношения к предметам с экрана S4 в state:', state.subjects);
            }

            switchStep(currentStep, targetStep);
        });
    });
};
