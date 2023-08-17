import colors from 'tailwindcss/colors'
import forms from '@tailwindcss/forms'
import typography from '@tailwindcss/typography'

export default {
  content: [
    './resources/**/*.blade.php',
    './vendor/filament/**/*.blade.php',
  ],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        danger: colors.orange,
        primary: colors.sky,
        success: colors.sky,
        warning: colors.yellow,
      },
    },
  },
  plugins: [
    forms,
    typography,
  ],
}
