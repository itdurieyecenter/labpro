/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./application/views/**/*.php",
    "./application/helpers/**/*.php",
    "./application/controllers/**/*.php",
    "./assets/js/**/*.js",
    "./asets/js/**/*.js"
  ],
  safelist: [
    'bg-blue-600', 'bg-blue-700', 'hover:bg-blue-700', 'focus:ring-blue-500',
    'bg-emerald-600', 'bg-emerald-700', 'bg-emerald-800', 'hover:bg-emerald-700', 'hover:bg-emerald-800', 'focus:ring-emerald-500',
    'bg-rose-600', 'bg-rose-700', 'hover:bg-rose-700', 'focus:ring-rose-500',
    'bg-amber-500', 'bg-amber-600', 'hover:bg-amber-600', 'focus:ring-amber-500',
    'bg-slate-600', 'bg-slate-700', 'hover:bg-slate-700', 'focus:ring-slate-500',
    'bg-cyan-600', 'bg-cyan-700', 'hover:bg-cyan-700', 'focus:ring-cyan-500',
    'text-blue-600', 'text-emerald-600', 'text-emerald-700', 'text-rose-600', 'text-amber-500', 'text-amber-600', 'text-slate-600', 'text-cyan-600',
    'border-blue-500', 'border-emerald-500', 'border-rose-500', 'border-amber-400', 'border-amber-500', 'border-slate-500', 'border-cyan-500',
    'bg-blue-50', 'bg-emerald-50', 'bg-rose-50', 'bg-amber-50', 'bg-slate-50', 'bg-cyan-50',
    'text-blue-700', 'text-emerald-700', 'text-emerald-800', 'text-rose-700', 'text-amber-700', 'text-amber-800', 'text-slate-700', 'text-cyan-700'
  ],
  theme: {
    extend: {
      boxShadow: {
        '2xs': '0 1px 2px 0 rgba(0, 0, 0, 0.03)',
        'xs': '0 1px 2px 0 rgba(0, 0, 0, 0.05)',
      },
      colors: {
        melayu: {
          hijau: {
            50: '#ecfdf5',
            100: '#d1fae5',
            200: '#a7f3d0',
            300: '#6ee7b7',
            400: '#34d399',
            500: '#10b981',
            600: '#059669', // Hijau Pucuk Melayu
            700: '#047857', // Hijau Melayu Riau Primer
            800: '#065f46',
            900: '#064e3b', // Hijau Lumut Tua
            950: '#022c22',
          },
          kuning: {
            50: '#fffbeb',
            100: '#fef3c7',
            200: '#fde68a',
            300: '#fcd34d',
            400: '#fbbf24',
            500: '#f59e0b', // Kuning Diraja Melayu
            600: '#d97706', // Emas Tua Lancang Kuning
            700: '#b45309',
            800: '#92400e',
            900: '#78350f',
          },
          merah: {
            50: '#fff1f2',
            100: '#ffe4e6',
            500: '#f43f5e',
            600: '#e11d48',
            700: '#be123c', // Merah Marun Melayu (Keberanian)
            800: '#9f1239',
            900: '#881337',
          }
        },
        brand: {
          50: '#f0fdf4',
          100: '#dcfce7',
          200: '#bbf7d0',
          300: '#86efac',
          400: '#4ade80',
          500: '#22c55e',
          600: '#16a34a',
          700: '#15803d',
          800: '#166534',
          900: '#14532d',
        },
        lis: {
          primary: '#047857',     // Hijau Melayu Riau
          primaryHover: '#065f46',
          gold: '#f59e0b',        // Kuning Emas Diraja
          goldHover: '#d97706',
          success: '#059669',     // Emerald-600
          danger: '#be123c',      // Merah Melayu
          warning: '#d97706',     // Amber-600
          secondary: '#475569',   // Slate-600
          info: '#0f766e',        // Teal-700
          surface: '#ffffff',
          bg: '#f8fafc',
          border: '#e2e8f0',
        }
      }
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
    require('@tailwindcss/aspect-ratio'),
  ],
}
