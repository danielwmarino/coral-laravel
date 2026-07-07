import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/Livewire/**/*.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Poppins', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                navy: {
                    DEFAULT: '#003470',
                    dark: '#00275a',
                    light: '#004a9c',
                },
                pink: {
                    DEFAULT: '#FC54AA',
                    hover: '#E0429A',
                    tint: '#FCE4F1',
                },
            },
        },
    },

    plugins: [forms],
};
