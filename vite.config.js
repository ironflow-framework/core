import { defineConfig } from "vite";
import FullReload from "vite-plugin-full-reload";

export default defineConfig({
  plugins: [
    FullReload([
      "resources/views/**/*.twig",
      "resources/js/**/*.js",
      "resources/css/**/*.css",
    ]),
  ],
  server: {
    origin: "http://localhost:5173",
    cors: {
      // the origin you will be accessing via browser
      origin: ["http://localhost:8000"],
      methods: ["GET", "POST", "PUT", "DELETE", "OPTIONS"],
      credentials: true,
    },
    hmr: {
      host: "localhost",
      protocol: "ws",
      port: 5173,
    },
    watch: {
      usePolling: true,
      ignored: [
        "**/node_modules/**",
        "**/vendor/**",
        "/resources/views/**/*.twig",
      ],
    },
  },
  base: "/assets/",
  build: {
    copyPublicDir: false,
    // generate .vite/manifest.json in outDir
    outDir: "./public/assets",
    assetsDir: "",
    manifest: true,
    sourcemap: true,
    rollupOptions: {
      input: {
        app: "/resources/js/app.js",
        styles: "/resources/css/app.css",
      },
      output: {
        manualChunks: {
          vendor: ["vue", "react"], // Si vous utilisez ces frameworks
        },
      },
    },
  },
  resolve: {
    alias: {
      "@": "/resources/js",
      "@css": "/resources/css",
      "@uploads": "/storage/uploads"
    },
  },
});
