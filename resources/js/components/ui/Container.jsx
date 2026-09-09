const WIDTHS = {
    // Reading width for prose-heavy pages.
    prose: 'max-w-3xl',
    // Default app width.
    app: 'max-w-5xl',
    // Catalog grids and marketing sections.
    wide: 'max-w-6xl',
};

/**
 * Horizontal gutters and max width in one place. The gutter grows with the
 * viewport instead of jumping at a breakpoint.
 */
export default function Container({ width = 'app', className = '', children }) {
    return (
        <div className={`mx-auto w-full px-4 sm:px-6 lg:px-8 ${WIDTHS[width]} ${className}`}>
            {children}
        </div>
    );
}
