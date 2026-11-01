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
            colors: {
                // Design tokens (clay / ink editorial identity)
                'z-bg':          '#FDFAF6',
                'z-surface':     '#FFFFFF',
                'z-low':         '#F7F4F0',
                'z-outline':     '#D4CFC7',
                'z-primary':     '#1A1A1A',
                'z-secondary':   '#6B665E',
                'z-tertiary':    '#9A958D',
                'z-accent':      '#C8956C',
                'z-accent-dark': '#B8845B',
                'z-accent-deep': '#7A5530',
                'z-alight':      '#F0D9B5',
                'z-danger':      '#A84936',
                'z-danger-dark': '#963E2D',
                'z-whatsapp':    '#25D366',
            },
            fontFamily: {
                serif: ['"Noto Serif"', 'Georgia', 'serif'],
                display: ['"Noto Serif"', 'Georgia', 'serif'],
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
