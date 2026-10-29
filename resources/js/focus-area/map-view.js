export function initializeMapControls(page) {
    const viewport = page.querySelector('[data-map-viewport]');
    const diagram = page.querySelector('[data-map-diagram]');
    const label = page.querySelector('[data-map-zoom-label]');
    if (!viewport || !diagram) return;

    let zoom = 1;
    const applyZoom = () => {
        diagram.style.width = zoom === 1 ? '100%' : `${Math.max(viewport.clientWidth, 1040) * zoom}px`;
        if (label) label.textContent = `${Math.round(zoom * 100)}%`;
    };

    page.querySelectorAll('[data-map-zoom]').forEach(button => {
        button.addEventListener('click', () => {
            if (button.dataset.mapZoom === 'fit') zoom = 1;
            else zoom = Math.min(2, Math.max(1, zoom + Number(button.dataset.mapZoom)));
            applyZoom();
        });
    });
}
