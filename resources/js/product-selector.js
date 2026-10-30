export default function registerProductSelector(Alpine) {
    Alpine.data('ravionProductSelector', (config = {}) => ({
        endpoint: config.endpoint || '/materials/product-search',
        catalogueOnly: Boolean(config.catalogueOnly),
        selectedId: config.value ? String(config.value) : '',
        selectedProduct: config.selectedProduct || null,
        query: '',
        results: [],
        loading: false,
        loadingMore: false,
        open: false,
        error: '',
        controller: null,
        debounceTimer: null,
        requestSequence: 0,
        page: 0,
        perPage: 30,
        total: 0,
        hasMore: false,
        lastSearch: '',

        init() {
            if (this.selectedProduct?.id) {
                this.selectedId = String(this.selectedProduct.id);
                this.query = this.selectedProduct.name || '';
            }
        },

        queueSearch() {
            window.clearTimeout(this.debounceTimer);
            this.controller?.abort();
            ++this.requestSequence;
            this.loading = false;
            this.loadingMore = false;
            this.results = [];
            this.page = 0;
            this.total = 0;
            this.hasMore = false;
            this.error = '';
            this.lastSearch = '';
            if (this.selectedId && this.query !== (this.selectedProduct?.name || '')) {
                this.selectedId = '';
                this.selectedProduct = null;
                this.$dispatch('ravion-product-cleared');
            }
            if (this.query.trim().length < 2) {
                this.open = false;
                return;
            }
            this.debounceTimer = window.setTimeout(() => this.search(), 250);
        },

        async search() {
            window.clearTimeout(this.debounceTimer);
            this.controller?.abort();
            ++this.requestSequence;
            this.results = [];
            this.page = 0;
            this.total = 0;
            this.hasMore = false;
            this.lastSearch = '';
            this.error = '';
            if (this.query.trim().length < 2) {
                this.open = false;
                this.loading = false;
                return;
            }
            await this.fetchPage(1, false);
        },

        async loadMore() {
            if (!this.hasMore || this.loading || this.loadingMore ||
                this.query.trim() !== this.lastSearch) return;
            await this.fetchPage(this.page + 1, true);
        },

        async fetchPage(page, append) {
            const term = this.query.trim();
            if (term.length < 2) return;
            if (append && this.loadingMore) return;
            const sequence = ++this.requestSequence;
            const controller = new AbortController();
            this.controller?.abort();
            this.controller = controller;
            if (append) this.loadingMore = true;
            else this.loading = true;
            this.error = '';
            this.open = true;

            try {
                const url = new URL(this.endpoint, window.location.origin);
                url.searchParams.set('q', term);
                if (this.catalogueOnly) {
                    url.searchParams.set('catalogue_only', '1');
                    url.searchParams.set('product_master', '1');
                }
                url.searchParams.set('page', String(page));
                url.searchParams.set('per_page', String(this.perPage));
                // Keep the legacy limit for endpoints still accepting it.
                url.searchParams.set('limit', String(this.perPage));
                const response = await fetch(url.toString(), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    signal: controller.signal,
                });
                if (!response.ok) throw new Error(`Product search failed (${response.status})`);
                const payload = await response.json();
                if (sequence !== this.requestSequence || term !== this.query.trim()) return;
                const incoming = Array.isArray(payload.data) ? payload.data : [];
                const seen = new Set(append ? this.results.map(p => String(p.id)) : []);
                const unique = incoming.filter(p => {
                    const id = String(p.id);
                    if (seen.has(id)) return false;
                    seen.add(id);
                    return true;
                });
                this.results = append ? [...this.results, ...unique] : unique;
                this.page = Number(payload.meta?.page ?? page);
                this.total = Number(payload.meta?.total ?? this.results.length);
                this.hasMore = typeof payload.meta?.has_more === 'boolean'
                    ? payload.meta.has_more
                    : false;
                this.lastSearch = term;
                this.open = true;
            } catch (error) {
                if (error.name === 'AbortError' || sequence !== this.requestSequence) return;
                console.error('Ravion Product Search Error:', error);
                this.error = 'Unable to search Products. Please try again.';
                if (!append) this.results = [];
                this.open = true;
            } finally {
                if (sequence === this.requestSequence) {
                    this.loading = false;
                    this.loadingMore = false;
                    this.controller = null;
                }
            }
        },

        resetSearchState() {
            window.clearTimeout(this.debounceTimer);
            this.controller?.abort();
            ++this.requestSequence;
            this.controller = null;
            this.results = [];
            this.page = 0;
            this.total = 0;
            this.hasMore = false;
            this.lastSearch = '';
            this.loading = false;
            this.loadingMore = false;
            this.open = false;
            this.error = '';
        },

        selectProduct(product) {
            this.resetSearchState();
            this.selectedProduct = product;
            this.selectedId = String(product.id);
            this.query = product.name || '';
            this.$dispatch('ravion-product-selected', { product });
        },

        clearProduct() {
            this.resetSearchState();
            this.selectedProduct = null;
            this.selectedId = '';
            this.query = '';
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
            const path = product?.bible_paths?.[0];
            return path?.trade_name || product?.product_group?.name || 'Unclassified';
        },

        typeName(product) {
            const path = product?.bible_paths?.[0];
            return path ? [path.main_name, path.sub_name].filter(Boolean).join(' / ')
                : (product?.product_type?.name || 'Unclassified');
        },
    }));
}
