import { defineConfig } from 'vite'
import { resolve } from 'path'

export default defineConfig( {
    root: resolve( __dirname, 'assets' ),

    build: {
        outDir:    resolve( __dirname, 'assets/dist' ),
        emptyOutDir: true,
        manifest: true,
        rollupOptions: {
            input: {
                main: resolve( __dirname, 'assets/ts/main.ts' ),
            },
        },
    },

    server: {
        port: 5173,
        strictPort: true,
        cors: true,
        watch: { usePolling: true },
    },
} )
