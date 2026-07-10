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
                amber: {
                    400: '#7BAF80',
                    500: '#7BAF80',
                },
            },
        },
    },

    plugins: [forms],
};
