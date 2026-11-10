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
          // Keep the heavy 3D stack out of the critical HTML/UI chunk. Match by path, not
          // by entry package: listing packages pulls their shared deps (e.g. zustand) into
          // the 3D chunk, which the main bundle would then have to preload.
          manualChunks(id) {
            // Vite's dynamic-import helper is used by drei too; keep it out of r3f.
            if (id.includes('vite/preload-helper') || id.includes('vite/modulepreload-polyfill')) return 'vendor'
            if (!id.includes('node_modules')) return
            // Shared by the UI and R3F; pin them to their own chunk or Rollup parks them in r3f.
            if (/node_modules\/(react|react-dom|scheduler|zustand|use-sync-external-store)\//.test(id)) return 'vendor'
            if (/node_modules\/three\//.test(id)) return 'three'
            if (/node_modules\/(@react-three|three-stdlib|camera-controls|maath|troika|@monogrid|meshline|stats)/.test(id)) return 'r3f'
            if (/node_modules\/(gsap|@gsap|lenis)\//.test(id)) return 'motion'
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
