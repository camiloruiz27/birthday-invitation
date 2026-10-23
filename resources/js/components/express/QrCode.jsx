import { useEffect, useState } from 'react';
import QRCode from 'qrcode';

/**
 * A QR code for `value`, drawn as an <img> (a data URL), so it can be long-
 * pressed, printed and read by a screen reader through its alt text.
 *
 * Always black on white with a real quiet zone (4 modules), whatever the
 * surrounding theme: a scanner needs contrast, not taste. The bitmap is
 * generated at 2x `size` so it stays sharp on a phone and when printed.
 * `fluid` makes it shrink to its container (up to `size`) instead of keeping
 * a fixed width.
 */
export default function QrCode({
    value,
    size = 224,
    alt = 'Código QR para unirse a la sala',
    fluid = false,
    className = '',
}) {
    const [src, setSrc] = useState(null);
    const [failed, setFailed] = useState(false);

    useEffect(() => {
        let cancelled = false;

        setSrc(null);
        setFailed(false);

        if (!value) {
            setFailed(true);

            return undefined;
        }

        QRCode.toDataURL(value, {
            width: size * 2,
            margin: 4,
            errorCorrectionLevel: 'M',
            color: { dark: '#000000', light: '#ffffff' },
        })
            .then((url) => {
                if (!cancelled) setSrc(url);
            })
            .catch(() => {
                if (!cancelled) setFailed(true);
            });

        return () => {
            cancelled = true;
        };
    }, [value, size]);

    // `fluid`: take the width of the container, up to `size` (the poster).
    const box = fluid
        ? { width: '100%', maxWidth: size, height: 'auto' }
        : { width: size, height: size, maxWidth: '100%' };

    if (failed) {
        return (
            <div
                style={box}
                className={`flex aspect-square items-center justify-center rounded-control border border-line-strong bg-white p-3 text-center text-xs text-black ${className}`}
            >
                No se pudo dibujar el QR. Usa el código o el enlace.
            </div>
        );
    }

    if (!src) {
        return (
            <div
                style={box}
                aria-hidden="true"
                className={`skeleton aspect-square rounded-control ${className}`}
            />
        );
    }

    return (
        <img
            src={src}
            alt={alt}
            width={size}
            height={size}
            style={box}
            className={`aspect-square rounded-control bg-white ${className}`}
        />
    );
}
