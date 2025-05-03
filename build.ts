await Bun.build({
  entrypoints: ['./client/main.ts'],
  outdir: './public/assets',
  minify: true,
  format: "esm",
});