# Datos del caso

Esta carpeta esta vacia a proposito. Coloca aqui, con estos nombres exactos, los
archivos `.md` del caso "Steve Jacobs" (el contenido no se modifica ni se
parafrasea en ningun lado del modulo, se renderiza verbatim):

```
data/
  victima.md
  sobres/
    sobre-1.md
    sobre-2.md
    sobre-3.md
  suspects/           (no se usa todavia en la Mecanica de linea de tiempo,
                       se deja lista para la Mecanica 7 mas adelante)
    elizabeth-foster.md
    sofia-reyes.md
    lucas-jacobs.md
    rachel-miller.md
    emily-johnson.md
    kevin-huang.md
    daniel-blake.md
    sarah-collins.md
    jeremy-burt-testigo.md
```

`ImmersionDemoSeeder` referencia `sobres/sobre-1.md`, `sobre-2.md` y `sobre-3.md`
por ruta relativa a esta carpeta (ver `App\Modules\Immersion\Support\CaseFileReader`).
Si el archivo no existe todavia, el correo/bandeja simplemente sale vacio para
ese evento en vez de romper el resto de la linea de tiempo.
