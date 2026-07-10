
import {type SubjectDiagnosticEntry} from "../types.js";

export const collectGradesFromS3 = (): SubjectDiagnosticEntry[] => {
    const entries: SubjectDiagnosticEntry[] = [];
    const tempMap: Record<string, {quarter?: number; year?: number}> = {};
    const gradeSelects = document.querySelectorAll<HTMLSelectElement>('#step-3 .subject-grade');

    gradeSelects.forEach((select) => {
        const subjectCode = select.getAttribute('data-subject');
        const gradeType = select.getAttribute('data-type');
        const value = parseInt(select.value, 10);

        if (!subjectCode || !gradeType) return;

        if (!tempMap[subjectCode]){
            tempMap[subjectCode] = {};
        }

        if (gradeType === 'quarter') tempMap[subjectCode].quarter = value;
        if (gradeType === 'year') tempMap[subjectCode].year = value;
    });
    for (const code in tempMap) {
        const data = tempMap[code];
        if (data.quarter !== undefined && data.year !== undefined) {
            entries.push({
                subjectCode: code,
                quarterGrade: data.quarter,
                annualGrade: data.year,
                attitude: null // Отношение пока пустое, заполним на следующем шаге
            });
        }
    }
    return entries;
};
