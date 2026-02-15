import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'primary': '#1973b8',
                'primary-dark': '#145d96',
                'primary-light': '#42a5f5',
                'secondary': '#EFF5FE',
                'grey-100': '#edf1f7',
                'grey-150': '#f5f5f5',
                'grey-200': '#ECEDEF',
                'grey-250': '#BDBDBD',
                'grey-300': 'rgb(125, 133, 143)',
                'grey-350': '#79818B',
                'grey-400': '#141F2F'
            }
        },
    },

    plugins: [forms],
};
