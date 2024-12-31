/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./vendor/tales-from-a-dev/flowbite-bundle/templates/**/*.html.twig",
    "./assets/tailwind/**/*.js",
    "./templates/**/*.html.twig",
    "./src/Twig/Components/**/*.php",
    "./src/Taig/Components/**/*.php",
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}
