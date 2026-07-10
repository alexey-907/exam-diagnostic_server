import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                teal: {
                    400: '#7BAF80',
                    500: '#7BAF80',
                    600: '#7BAF80',
                },
                purple: {
                    400: '#7BAF80',
                    500: '#7BAF80',
                },
                blue: {
                    600: '#7BAF80',
                    700: '#73AB7B',
                },
                amber: {
                    400: '#7BAF80',
                    500: '#7BAF80',
                },
                brand: {
                    bg: '#FEF5ED',           // Основной фон экрана анкеты
                    input: '#FFFDF7',        // Поля ввода (инпуты)
                    btnDisabled: '#B9CBB6',  // Неактивная кнопка "Далее"
                    btnActive: '#97D58B',    // Активная кнопка "Далее"
                    select: '#F5FCF6',       // Селекты (выпадающие списки)
                    radioChecked: '#AED8A5', // Выбранная радио-кнопка (без #, добавили хэш)
                    btnSubmit: '#C98D52',
                    backBtn: '#7BAF80',

                    // Кнопка подтверждения на вопросах в тесте
                }
            },
        },
    },

    plugins: [forms],
};
