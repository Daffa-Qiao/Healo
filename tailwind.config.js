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
            fontFamily: {
                sans: ['Inter', 'ui-sans-serif', ...defaultTheme.fontFamily.sans],
            },
            boxShadow: {
                soft: '0 24px 70px -35px rgb(15 23 42 / 0.28)',
            },
        },
    },

    plugins: [forms],
};
