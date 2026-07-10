export type ExamType = 'OGE' | 'EGE' | 'EARLY';
export type Role = 'student' | 'parent' | 'teacher';
export type TargetTrack = 'UNIVERSITY' | 'COLLEGE' | 'PROFILE_CLASS';

// Итоговая структура данных, объединяющая S1 и S2
export interface SubjectAttitude {
    detailedAnswers: Record<string, number>; // ключ: ID_вопроса, значение: оценка 1-5
}

export interface SubjectDiagnosticEntry {
    subjectCode: string;
    quarterGrade: number;
    annualGrade: number;
    attitude: SubjectAttitude | null;
}
export interface DiagnosticState {
    sessionId: string | null;
    role: Role | null;
    grade: number | null;
    examType: ExamType | null;
    region: string | null;
    targetYear: number | null;
    targetTrack: TargetTrack | null;
    subjects: SubjectDiagnosticEntry[];

    cognitive: {
        wmScore: number;
        vmScore: number;
        vmImmediateWords: string[];
        lrScore: number;
        arScore: number;
        vrScore: number;
        spScore: number;
        attScore: number; // ВАЖНО: Добавлен балл внимания и саморегуляции (0-100) по ТЗ
    } | null;

    interests: string[];

}

export interface WMTask {
    code: string;
    instruction: string;
    displayData: string;
    correctAnswer: string;
}

export interface LRQuestion {
    code: string;
    question: string;
    options: { key: string; text: string }[];
    correctAnswer: string;
    explanation: string;
}

export interface ARTask {
    code: string;
    taskText: string;
    correctAnswer: string;
}

export interface VRQuestion {
    code: string;
    question: string;
    options: { key: string; text: string }[];
    correctAnswer: string;
}

export interface SPQuestion {
    code: string;
    question: string;
    correctAnswers: string[]; // Массив возможных синонимов ответа для надежности
}

export interface SELFTask {
    code: string;
    type: 'scale-1-5' | 'custom-options' | 'select-days';
    badge: string;
    question: string;
    options?: { val: number; text: string }[];
}
