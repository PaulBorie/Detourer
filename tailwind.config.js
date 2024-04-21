// tailwind.config.js
import typography from '@tailwindcss/typography';
import forms from '@tailwindcss/forms';
import aspectRatio from '@tailwindcss/aspect-ratio';



/** @type {import('tailwindcss').Config} */
export default {

  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
  ],
  theme: {
    extend: {

      fontFamily: {
        'inter-var': ['"Inter"', 'sans-serif'],
      },
      screens: {
        'xl': '1500px',
        // => @media (min-width: 992px) { ... }
      },
      colors: {
        rose: {
          '50': '#fef2f4',
          '100': '#fde6e9',
          '200': '#fbd0d9',
          '300': '#f7aab9',
          '400': '#f27a93',
          '500': '#e63f66',
          '600': '#d42a5b',
          '700': '#b21e4b',
          '800': '#951c45',
          '900': '#801b40',
          '950': '#470a1f',
        },
      
        noir: {
          '500': '#18181b',
          '900': '#0e0e11'
        },
        linkedin: {
          '100': '#0e76a8'
        }
      }
    },
  },
  plugins: [
    typography,
    forms,
    aspectRatio,
  ],
}

//https://online-free-tools.com/en/css_pattern_generator_background