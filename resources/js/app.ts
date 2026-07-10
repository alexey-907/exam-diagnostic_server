
import {initNavigation } from './navigation.js';
import { initRegionAutocomplete } from './regions.js';
import { initStep4Attitude } from './inizilization/4_attitude.js';
import { initStep5WM } from './tests/5_wm_test.js';
import { initStep6VM } from './tests/6_vm_test.js';
import { initStep7LR } from './tests/7_lr_test.js';
import { initStep8AR } from './tests/8_ar_test.js';
import { initStep9VR } from './tests/9_vr_test.js';
import { initStep10SP } from './tests/10_sp_test.js';
import { initStep11SELF } from './tests/11_self_test.js';
import { initResultsModule } from './results.js';
document.addEventListener('DOMContentLoaded', () => {
    console.log('все норм');

    // Инициализируем автодополнение регионов DaData прокси
    initRegionAutocomplete();

    // Инициализируем универсальный цикл кнопок Далее/Назад и запись S1-S3
    initNavigation();

    // Инициализируем спойлеры анкеты предметов на 4 шаге
    initStep4Attitude();

    // Инициализируем независимые игровые движки когнитивных тестов по ТЗ
    initStep5WM();
    initStep6VM();
    initStep7LR();
    initStep8AR();
    initStep9VR();
    initStep10SP();
    initStep11SELF();

    // Инициализируем финальный обработчик кластеров, fetch-отправку и Scoring рендер
    initResultsModule();
});
