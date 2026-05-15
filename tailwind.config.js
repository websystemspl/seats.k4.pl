/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
    "./node_modules/tw-elements/dist/js/**/*.js"
  ],
  theme: {
    letterSpacing: {
      widest: '.2em',
    },
    extend: {
      colors: {
        'k4pink': '#ff497c',
        'k4green': '#a0ce4e',
        'k4dark': '#1f2732',
        'k4yellow': '#dd9933',
        'k4red': '#e00040',
        'k4gray': '#C8C8C8'
      },
    },
  },
  plugins: [
    require('tw-elements/dist/plugin')
  ],
}
