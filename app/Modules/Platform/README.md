# Platform

La capa **alrededor** del motor de juego: catálogo, propiedad y comercio.

## Frontera con el motor

La dependencia va en **un solo sentido**:

```
Platform  ──lee──►  Immersion (manifiestos de caso, partidas)
Platform  ◄──────   Immersion   ✗ nunca
```

El motor no sabe qué es un producto, un precio ni una cuenta. La unión entre
las dos capas es el **slug del caso**, no una foreign key: una `Game` guarda
`case_slug`, no `mystery_case_id`. Por eso el motor sigue funcionando aunque
la fila del catálogo no exista todavía (y por eso se puede jugar un caso que
no está publicado).

## Catálogo: qué manda sobre qué

El contenido de un caso vive en su manifiesto (`Immersion/Cases/<slug>/case.php`,
versionado en git). La tabla `mystery_cases` guarda solo lo que hay que
consultar o cruzar: precio, estado de publicación y el resumen que ve el
comprador.

`php artisan platform:sync-cases` publica los manifiestos instalados en el
catálogo. Es idempotente y corre en cada deploy.

| Campos | Fuente de verdad | Al sincronizar |
|---|---|---|
| `name`, `tagline`, `description`, `cover_path`, `difficulty`, `duration_minutes`, `min_players`, `max_players`, `mechanics`, `content_version` | El manifiesto (git) | **Se reescriben** siempre |
| `price_amount`, `currency`, `published_at`, `sort_order` | La base de datos | **Se respetan**; solo se siembran al crear la fila |

La razón de la asimetría: cambiar un precio o sacar un caso de venta no debe
requerir un deploy, y un deploy no debe deshacer esa decisión.

```bash
php artisan platform:sync-cases              # uso normal (deploy)
php artisan platform:sync-cases --dry-run    # ver qué cambiaría, sin escribir
php artisan platform:sync-cases --prices     # reaplicar a propósito precio y publicación del manifiesto
```

## Precios

`price_amount` es un entero en la **unidad mínima de la moneda**. El COP no usa
centavos en la práctica, así que para `currency = COP` son pesos enteros
(`89000` = $89.000 COP).

> El precio actual de `steve-jacobs` es un **placeholder** puesto en el
> manifiesto. Ajústalo antes de vender, en la base de datos o en el manifiesto
> con `--prices`.

## Recargas de créditos de IA

Segunda línea de ingreso, además del caso. Se venden en paquetes declarados en
`config/platform.php` (`credit_packages`) — también con precios placeholder.

La división es deliberada: **vender** créditos es comercio y vive aquí; **qué
compra** un crédito es una propiedad del motor y vive en `config/immersion.php`.
Así la dirección de dependencia se mantiene — la plataforma lee el motor, nunca
al revés — y el monedero puede existir aunque no haya nada que vender.

Adquirir un caso incluye los créditos para jugarlo **al máximo** una vez, y
**una sola vez por caso**: reintentar un webhook de compra no acuña créditos.

La cantidad no está en config: se deriva del propio caso — todas las preguntas
que permite su elenco más su final más caro (`GameCost::maxForCase`). Para
steve-jacobs son 9 × 5 + 15 = **60**. Un caso con doce sospechosos vendrá con
más, sin tocar nada.

Mientras `simulated_checkout` esté encendido, la recarga se entrega sin cobrar,
igual que los casos. Ver [Immersion/README.md](../Immersion/README.md) §4.2.3
para cómo se reservan y se devuelven.

## Cuentas y acceso

Hay dos tipos de participante y **no** se mezclan:

| | Identidad | Puede |
|---|---|---|
| **`User`** | Cuenta con correo y contraseña | Comprar casos, crear y dirigir partidas |
| **`Player`** | Solo un `access_token` en la URL | Jugar la partida a la que fue invitado |

Un jugador nunca necesita cuenta. Por eso las rutas de `/jugador/{token}` no
piden `auth`, y las de `/gm` sí.

### Acceso a un caso

El acceso se lee **siempre** de `Entitlement`, nunca de una orden ni de un
pago. Una compra, un regalo manual y una futura suscripción todos terminan
escribiendo un entitlement, así que el modelo comercial puede cambiar sin
tocar una sola verificación de permisos.

`GrantCaseAccess` es la única puerta que escribe entitlements. Es idempotente:
reintentar un webhook de compra no crea un segundo derecho.

```bash
php artisan platform:grant-access tu@correo.com steve-jacobs
php artisan platform:grant-access tu@correo.com steve-jacobs --revoke
```

Crear una cuenta **no** incluye ningún caso.

### Propiedad de partidas

Una partida pertenece a la cuenta que la creó (`immersion_games.user_id`).
`GamePolicy` lo verifica en el servidor, declarado en las rutas con
`can:view,game` / `can:control,game` para que una acción nueva no pueda
publicarse sin autorización por descuido.

Las partidas creadas antes de que existieran las cuentas tienen `user_id`
nulo y **no son alcanzables por web** — la policy niega las partidas sin
dueño en vez de dejar que se las quede el primero que abra la URL. Se
asignan a mano, que es un paso deliberado y auditable:

```bash
php artisan platform:claim-games tu@correo.com --dry-run
php artisan platform:claim-games tu@correo.com
```

## Páginas públicas

`/` (landing), `/casos`, `/casos/{slug}`, `/mecanicas`,
`/inteligencia-artificial`, `/precios`.

Son públicas de verdad: un usuario con sesión iniciada que navega el catálogo
se queda en ellas, no se le rebota al panel. `PublicLayout` cambia solo sus
llamados a la acción.

El catálogo solo muestra casos publicados — un caso sin publicar da 404 aunque
exista la fila. Lo que cruza al navegador se arma en `CaseCardData`, con la
lista de campos explícita (no se serializa el modelo), porque estas páginas son
públicas.

### Mecánicas

`Support/Mechanics` es el vocabulario: qué significa `interrogation`, qué hace,
y si usa IA. Vive en la plataforma y no en un caso porque una mecánica es una
capacidad del motor a la que los casos se **suscriben** — la misma mecánica
tiene que significar lo mismo en la página de todos los casos.

Un caso declara sus mecánicas como slugs en su manifiesto; `Mechanics::describe()`
los resuelve e **ignora los que no conoce**, para que un slug nuevo no rompa una
página de catálogo.

## Adquisición simulada

Todavía no hay pasarela de pagos. Mientras `platform.simulated_checkout` esté
encendido, un usuario con sesión puede meter un caso en su biblioteca desde la
página del caso sin pagar, para poder recorrer el embudo completo.

- **Apagado en producción por defecto**: un sitio desplegado no debe regalar
  casos. Con la simulación apagada la ruta da 404.
- No pide ni un solo dato de pago.
- Escribe un entitlement normal con `source = grant`, así que un acceso
  simulado siempre se distingue de una compra real en los datos.
- La interfaz lo dice explícitamente. Nunca debe parecer una compra de verdad.

## Estado

Implementado: catálogo (`mystery_cases`, sync), cuentas (registro, ingreso,
recuperación de contraseña, perfil), `Entitlement`, propiedad de partidas,
páginas públicas y adquisición simulada.

Pendiente: `Order`/`Payment` como conceptos, checkout real, y la integración de
pagos con Bold. Cuando llegue, lo único que tiene que hacer es llamar a
`GrantCaseAccess` con `source = purchase`; ninguna verificación de acceso
cambia.
