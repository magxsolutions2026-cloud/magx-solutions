import { defineConfig, loadEnv } from 'vite'
import react from '@vitejs/plugin-react'
import tailwindcss from '@tailwindcss/vite'

// The build is emitted to ../site so Vercel can serve it next to the PHP backend
// (uploads/, api/*.php) from the repo root. See ../vercel.json.
export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '')
  // Local PHP backend for the *_api.php endpoints, e.g. `php -S localhost:8000` in the repo root.
  const phpTarget = env.VITE_PHP_PROXY || 'http://localhost:8000'

  return {
    base: '/site/',
    plugins: [react(), tailwindcss()],
    build: {
      outDir: '../site',
      emptyOutDir: true,
      target: 'es2022',
      rollupOptions: {
        output: {
          // Keep the heavy 3D stack out of the critical HTML/UI chunk.
          manualChunks: {
            three: ['three'],
            r3f: ['@react-three/fiber', '@react-three/drei'],
            motion: ['gsap', '@gsap/react', 'motion', 'lenis'],
          },
        },
      },
    },
    server: {
      proxy: {
        '^/[a-z_]+_api\\.php$': { target: phpTarget, changeOrigin: true },
        '^/uploads/': { target: phpTarget, changeOrigin: true },
      },
    },
  }
})
