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

## Estado

Implementado: catálogo (`mystery_cases`, `MysteryCase`, sync).

Pendiente: cuentas (`User`), `Entitlement` (acceso permanente por caso),
`Order`/`Payment` como conceptos, y la integración de pagos con Bold. El acceso
a un caso se resolverá contra `Entitlement`, **nunca** contra una transacción de
pago, para que el modelo comercial pueda cambiar sin tocar el acceso.
