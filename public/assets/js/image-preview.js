document.addEventListener('DOMContentLoaded', function () {
    // ── PREVIEW DE LA GALERÍA ──
    const galleryInput = document.getElementById('Product_imagesUpload');
    if (galleryInput) {
        let box = document.querySelector('.v-gallery-preview-box');
        if (!box) {
            box = document.createElement('div');
            box.className = 'v-gallery-preview-box mt-3 mb-4 d-flex flex-wrap gap-3 p-3 bg-light rounded-4 border border-dashed shadow-sm d-none';
            const parent = galleryInput.closest('.form-group') || galleryInput.parentNode.parentNode;
            parent.appendChild(box);
        }

        galleryInput.addEventListener('change', function () {
            box.innerHTML = '';
            const files = Array.from(galleryInput.files);
            if (files.length === 0) { box.classList.add('d-none'); return; }
            box.classList.remove('d-none');

            files.slice(0, 10).forEach(file => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.width = '100px'; img.style.aspectRatio = '1/1'; img.style.objectFit = 'cover'; img.style.borderRadius = '12px';
                    box.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        });
    }

    // ── PREVIEW DE PORTADA (Reforzado) ──
    const coverInput = document.querySelector('input[type="file"][id*="_image"]');
    if (coverInput) {
        let cBox = document.querySelector('.v-cover-preview-box');
        if (!cBox) {
            cBox = document.createElement('div');
            cBox.className = 'v-cover-preview-box mt-3 mb-3 d-inline-block d-none';
            // Insertar después del input o de su contenedor de EA
            const parent = coverInput.closest('.form-widget') || coverInput.parentNode;
            parent.appendChild(cBox);
        }

        coverInput.addEventListener('change', function () {
            cBox.innerHTML = '';
            if (!this.files || !this.files[0]) { cBox.classList.add('d-none'); return; }
            cBox.classList.remove('d-none');
            const reader = new FileReader();
            reader.onload = (e) => {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.style.width = '120px';
                img.style.padding = '5px';
                img.style.border = '2px solid #6366f1';
                img.style.borderRadius = '15px';
                img.style.background = '#fff';
                img.style.boxShadow = '0 5px 15px rgba(0,0,0,0.1)';
                cBox.appendChild(img);
            };
            reader.readAsDataURL(this.files[0]);
        });
    }

    // ── RESTRICCIÓN DE PRECIOS ──
    const initPriceRestriction = (id) => {
        const input = document.getElementById(id);
        if (!input) return;

        input.addEventListener('input', function () {
            // Reemplazar todo lo que no sea dígito o coma
            let value = this.value.replace(/[^0-9,]/g, '');

            // Asegurar un solo punto decimal (coma)
            const parts = value.split(',');
            if (parts.length > 2) {
                value = parts[0] + ',' + parts.slice(1).join('');
            }

            if (this.value !== value) {
                this.value = value;
            }
        });

        // Opcional: convertir puntos a comas automáticamente al escribir
        input.addEventListener('keydown', function (e) {
            if (e.key === '.') {
                e.preventDefault();
                const pos = this.selectionStart;
                this.value = this.value.slice(0, pos) + ',' + this.value.slice(pos);
                this.selectionStart = this.selectionEnd = pos + 1;
                // Disparar input para que se aplique la limpieza si hace falta
                this.dispatchEvent(new Event('input'));
            }
        });
    };

    initPriceRestriction('Product_price');
    initPriceRestriction('Product_oldPrice');
    initPriceRestriction('Product_purchaseCost');

    // ── GESTIÓN DE STOCK CONDICIONAL ──
    const initStockSync = () => {
        const mainStockInput = document.getElementById('Product_stock');
        if (!mainStockInput) return;

        const checkVariantStocks = () => {
            let hasVariantStock = false;
            document.querySelectorAll('.variant-stock-input').forEach(input => {
                if (input.value.trim() !== '' && parseInt(input.value) >= 0) {
                    hasVariantStock = true;
                }
            });

            if (hasVariantStock) {
                mainStockInput.disabled = true;
                mainStockInput.dataset.originalValue = mainStockInput.value;
                mainStockInput.value = '';
                mainStockInput.placeholder = 'Calculado por opciones...';

                // Efecto visual para EasyAdmin
                const helpText = mainStockInput.closest('.form-group')?.querySelector('.form-help');
                if (helpText) helpText.innerHTML = '<span class="text-primary fw-bold"><i class="fas fa-magic"></i> El stock se está manejando desde las variantes de arriba!.</span>';
            } else {
                mainStockInput.disabled = false;
                mainStockInput.placeholder = '';

                const helpText = mainStockInput.closest('.form-group')?.querySelector('.form-help');
                if (helpText) helpText.innerHTML = 'Deja en blanco si el producto tiene stock infinito.'; // Valor por defecto
            }
        };

        checkVariantStocks();
        document.addEventListener('input', (e) => {
            if (e.target.classList.contains('variant-stock-input')) checkVariantStocks();
        });

        // Timeout para elementos generados por AJAX en colecciones
        setInterval(checkVariantStocks, 1500);
    };

    initStockSync();
});
