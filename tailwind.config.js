/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/js/**/*.{vue,js}',
        './resources/views/spa.blade.php',
    ],
    theme: {
        extend: {
            colors: {
                'run-blue': '#282870',
                'run-gold': '#E0D35E',
                'run-dark': '#1e1e5a',
            },
        },
    },
    plugins: [require('@tailwindcss/forms')],
};
