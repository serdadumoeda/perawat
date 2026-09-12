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
                sans:    ['Inter', ...defaultTheme.fontFamily.sans],
                display: ['Playfair Display', 'Georgia', 'serif'],
                serif:   ['Playfair Display', 'Georgia', 'serif'],
            },
            colors: {
                // Botanical Earth Palette
                botanical: {
                    50:  '#f2f7f4',
                    100: '#d8e5dd',
                    200: '#b4cebf',
                    300: '#84ad9a',
                    400: '#557a58',
                    500: '#3d6756',
                    600: '#2a5546',
                    700: '#1e483b',
                    800: '#143a30',
                    900: '#0f2922',
                },
                sage: {
                    100: '#e3ebe1',
                    300: '#799a82',
                    500: '#557a58',
                    700: '#365339',
                },
                earth: {
                    mocha:      '#573d33',
                    chocolate:  '#48342b',
                    terracotta: '#a16d49',
                    caramel:    '#b07850',
                    sand:       '#e3d1b0',
                    cream:      '#f4efe6',
                    bg:         '#f4efe6',
                },
                gold: {
                    pill:       '#d8a47f',
                    pillHover:  '#c6926d',
                    light:      '#ebd3c2',
                },
                coral: {
                    50:  '#fff5f2',
                    100: '#fde8e0',
                    300: '#f7a58a',
                    400: '#f47c5a',
                    500: '#e05252',
                    600: '#c0392b',
                },
                teal: {
                    100: '#c8eded',
                    400: '#1aa8a8',
                    500: '#148080',
                    600: '#116060',
                    700: '#0f5050',
                    800: '#0d4040',
                    900: '#0b3535',
                },
                cream: {
                    50:  '#f4efe6',
                    100: '#eae2d3',
                    200: '#e3d1b0',
                    300: '#cbb793',
                },
                // Aliases for backward compat with existing views
                brand: {
                    50:  '#f2f7f4',
                    100: '#d8e5dd',
                    200: '#b4cebf',
                    300: '#84ad9a',
                    400: '#557a58',
                    500: '#1e483b',
                    600: '#143a30',
                    700: '#0f2922',
                    800: '#0b1d18',
                    900: '#081511',
                },
                accent: {
                    100: '#ebd3c2',
                    200: '#e3d1b0',
                    300: '#d8a47f',
                    400: '#a16d49',
                    500: '#573d33',
                    600: '#48342b',
                    700: '#36241c',
                    800: '#251711',
                },
            },
            borderRadius: {
                '4xl': '2rem',
                '5xl': '2.5rem',
            },
        },
    },

    plugins: [forms],
};
