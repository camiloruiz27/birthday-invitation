<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    {{-- Self-contained on purpose: this page is shown when the database refuses
         connections, so it cannot go through Inertia (whose shared props read
         the logged-in user from that same database) or depend on the app's
         bundle. Inline styles only, no scripts. --}}
    <title>Mucha demanda — MisterioCode</title>
</head>
<body style="margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;background:#0f1115;color:#e8e6e1;font-family:system-ui,-apple-system,'Segoe UI',sans-serif;">
    <main style="max-width:34rem;padding:2rem 1.5rem;">
        <p style="margin:0 0 1rem;font-size:.8rem;letter-spacing:.12em;text-transform:uppercase;color:#c9a227;">Expediente no disponible</p>
        <h1 style="margin:0 0 1rem;font-size:1.9rem;line-height:1.2;">Mucha demanda en este momento</h1>
        <p style="margin:0 0 1rem;line-height:1.6;color:#b9b6ae;">
            El expediente está a salvo, pero hay demasiadas personas investigando a la vez.
            Espera unos minutos y vuelve a abrir esta página: tu avance no se perdió.
        </p>
        <p style="margin:0;line-height:1.6;color:#b9b6ae;">
            Si estás en una partida, avisa a tu Game Master. Nada de lo que hiciste se descontó.
        </p>
    </main>
</body>
</html>
