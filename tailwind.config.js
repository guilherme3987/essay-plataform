/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
      "./*.php", 
      "./pages/**/*.php", 
      "./api/**/*.php" 
    ],
    theme: {
      extend: {
        fontFamily: {
          sans: ['Outfit', 'sans-serif'],
        },
      },
    },
    plugins: [],
  }