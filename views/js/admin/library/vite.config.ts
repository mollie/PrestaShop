import { defineConfig } from 'vite'
import type { Plugin } from 'vite'
import react from '@vitejs/plugin-react'
import fs from 'fs'
import path from 'path'

// PrestaShop expects stylesheets under /views/css. Vite emits them beside the JS
// entries, so move them out of the bundle and into their own directory there.
const stylesheetOutputDir = path.resolve(__dirname, '../../../css/admin/library')

function emitStylesheetsToViewsCss(): Plugin {
  return {
    name: 'mollie-stylesheets-to-views-css',
    generateBundle(_options, bundle) {
      for (const [fileName, output] of Object.entries(bundle)) {
        if (output.type !== 'asset' || !fileName.endsWith('.css')) {
          continue
        }

        fs.mkdirSync(stylesheetOutputDir, { recursive: true })
        fs.writeFileSync(path.join(stylesheetOutputDir, path.basename(fileName)), output.source)

        delete bundle[fileName]
      }
    },
  }
}

// https://vite.dev/config/
export default defineConfig({
  plugins: [react(), emitStylesheetsToViewsCss()],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'src'),
      '@/shared': path.resolve(__dirname, 'src/shared'),
      '@/components': path.resolve(__dirname, 'src/shared/components'),
      '@/ui': path.resolve(__dirname, 'src/shared/components/ui'),
      '@/lib': path.resolve(__dirname, 'src/shared/lib'),
      '@/hooks': path.resolve(__dirname, 'src/shared/hooks'),
      '@/types': path.resolve(__dirname, 'src/shared/types'),
      '@/pages': path.resolve(__dirname, 'src/pages'),
      '@/app': path.resolve(__dirname, 'src/app'),
    },
  },
  build: {
    rollupOptions: {
      input: {
        // Authorization page entry point
        authorization: path.resolve(__dirname, 'src/app/authorization.tsx'),
        // Payment Methods page entry point
        'mollie-payment-methods': path.resolve(__dirname, 'src/app/payment-methods.tsx'),
        // Advanced Settings page entry point
        'mollie-advanced-settings': path.resolve(__dirname, 'src/app/advanced-settings.tsx'),
      },
      output: {
        entryFileNames: 'assets/[name].js',
        chunkFileNames: 'assets/[name]-[hash].js',
        assetFileNames: 'assets/[name].[ext]',
        banner: [
          '/**',
          ' * Mollie       https://www.mollie.nl',
          ' *',
          ' * @author      Mollie B.V. <info@mollie.nl>',
          ' * @copyright   Mollie B.V.',
          ' * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md',
          ' *',
          ' * @see        https://github.com/mollie/PrestaShop',
          ' */',
        ].join('\n'),
      },
    },
  },
})
