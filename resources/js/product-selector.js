export default function registerProductSelector(Alpine) {
    Alpine.data('ravionProductSelector', (config = {}) => ({
        endpoint: config.endpoint || '/materials/product-search',
        selectedId: config.value ? String(config.value) : '',
        selectedProduct: config.selectedProduct || null,
        query: '',
        results: [],
        loading: false,
        open: false,
        error: '',
        controller: null,
        debounceTimer: null,

        init() {
            if (this.selectedProduct && this.selectedProduct.id) {
                this.selectedId = String(this.selectedProduct.id);
                this.query = this.selectedProduct.name || '';
            }
        },

        queueSearch() {
            window.clearTimeout(this.debounceTimer);
            this.debounceTimer = window.setTimeout(() => this.search(), 250);
        },

        async search() {
            const term = this.query.trim();

            if (term.length < 2) {
                this.results = [];
                this.open = false;
                this.error = '';
                this.controller?.abort();
                this.controller = null;
                return;
            }

            this.controller?.abort();
            this.controller = new AbortController();
            this.loading = true;
            this.error = '';
            this.open = true;

            try {
                const url = new URL(this.endpoint, window.location.origin);
                url.searchParams.set('q', term);
                url.searchParams.set('limit', '30');

                const response = await fetch(url.toString(), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    signal: this.controller.signal,
                });

                if (!response.ok) {
                    throw new Error(`Product search failed with status ${response.status}`);
                }

                const payload = await response.json();
                this.results = Array.isArray(payload.data) ? payload.data : [];
                this.open = true;
            } catch (error) {
                if (error.name === 'AbortError') return;
                console.error('Ravion Product Search Error:', error);
                this.results = [];
                this.error = 'Unable to search Products. Please try again.';
                this.open = true;
            } finally {
                this.loading = false;
                this.controller = null;
            }
        },

        selectProduct(product) {
            this.selectedProduct = product;
            this.selectedId = String(product.id);
            this.query = product.name || '';
            this.results = [];
            this.open = false;
            this.error = '';
            this.$dispatch('ravion-product-selected', { product });
        },

        clearProduct() {
            this.controller?.abort();
            this.selectedProduct = null;
            this.selectedId = '';
            this.query = '';
            this.results = [];
            this.open = false;
            this.error = '';
            this.$dispatch('ravion-product-cleared');
            this.$nextTick(() => this.$refs.searchInput?.focus());
        },

        closeResults() {
            window.setTimeout(() => { this.open = false; }, 180);
        },

        unitLabel(product) {
            return product?.unit?.code || product?.unit?.symbol || product?.unit?.name || '—';
        },

        groupName(product) {
            return product?.product_group?.name || 'Unclassified';
        },

        typeName(product) {
            return product?.product_type?.name || 'Unclassified';
        },
    }));
}
