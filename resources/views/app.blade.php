<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Feriaza</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alegreya:wght@700;800&family=Alegreya+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <script>
      // Antes de pintar nada, para que no se vea un parpadeo claro->oscuro
      (function () {
        try {
          var guardado = localStorage.getItem('fz-tema');
          var tema = guardado ?? (matchMedia('(prefers-color-scheme: dark)').matches ? 'oscuro' : 'claro');
          document.documentElement.setAttribute('data-tema', tema);
        } catch (e) {}
      })();
    </script>
    @vite('resources/js/app.js')
    @inertiaHead
  </head>
  <body>
    @inertia
  </body>
</html>
