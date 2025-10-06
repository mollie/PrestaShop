import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import path from 'path'

// https://vite.dev/config/
export default defineConfig({
  plugins: [react()],
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
      },
      output: {
        entryFileNames: 'assets/[name].js',
        chunkFileNames: 'assets/[name]-[hash].js',
        assetFileNames: 'assets/[name].[ext]',
      },
    },
  },
})
