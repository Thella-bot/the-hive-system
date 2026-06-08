import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    darkMode: 'class',

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'hbci-gold': {
                    DEFAULT: '#f5c842',
                    'dark': '#d4a503',
                    'light': '#fef3c7',
                },
                'hbci-gray': {
                    DEFAULT: '#6b7280',
                    'dark': '#374151',
                    'light': '#f3f4f6',
                },
                'hbci-cream': '#fefce8',
                'hbci-warm': {
                    50: '#fefce8',
                    100: '#fef9c3',
                    200: '#fef08a',
                    300: '#fde047',
                    400: '#facc15',
                    500: '#f5c842',
                    600: '#eab308',
                    700: '#ca8a04',
                    800: '#a16207',
                    900: '#854d0e',
                },
            },
        },
    },

    plugins: [forms, typography],
};