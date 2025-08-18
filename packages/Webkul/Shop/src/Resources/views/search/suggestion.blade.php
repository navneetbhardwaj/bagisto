<v-suggestion-searchbar></v-suggestion-searchbar>

@pushOnce('scripts')
<script type="text/x-template" id="v-suggestion-searchbar-template">
    <div>
        <div class="relative w-full">
            <div class="flex max-w-[445px] items-center">
                <form
                    action="{{ route('shop.search.index') }}"
                    class="flex max-w-[445px] items-center"
                    role="search"
                >
                    <label
                        for="organic-search"
                        class="sr-only"
                    >
                        @lang('shop::app.components.layouts.header.desktop.bottom.search')
                    </label>
                    <div class="icon-search pointer-events-none absolute top-2.5 flex items-center text-xl ltr:left-3 rtl:right-3"></div>
                    <input
                        type="text"
                        name="query"
                        value="{{ request('query') }}"
                        class="block w-full rounded-lg border border-transparent bg-[#F5F5F5] px-11 py-3 text-xs font-medium text-gray-900 transition-all hover:border-gray-400 focus:border-gray-400"
                        placeholder="@lang('shop::app.components.layouts.header.desktop.bottom.search-text')"
                        aria-label="@lang('shop::app.components.layouts.header.desktop.bottom.search-text')"
                        aria-required="true"
                        v-model="term"
                        autocomplete="off"
                        onblur="setTimeout(function() {if(document.querySelector('#suggest')){document.querySelector('#suggest').classList.add('hidden')}}, 300)"
                        onfocus="if(document.querySelector('#suggest'))document.querySelector('#suggest').classList.remove('hidden')"
                        @keyup="search()"
                        required
                    >
                    @if (core()->getConfigData('catalog.products.settings.image_search'))
                        @include('shop::search.images.index')
                    @endif
                    <button
                        class="btn"
                        type="button"
                        id="header-search-iconi"
                        aria-label="Search"
                        @click="submitForm"
                    >
                    </button>
                </form>
            </div>
        </div>
        <div
            class="absolute z-10 max-h-96 w-full overflow-auto bg-white shadow-lg pb-1 flex flex-col m-0 p-0  "
            id="suggest"
            v-if="term.length >= config.minSearchTerms"
        >
            <div
                :class="config.display === 'ar' ? 'ar' : ''"
                v-if="suggestsResults.length"
            >
                <span
                    v-for="(result, index) in suggestsResults"
                >
                    <div v-if="index < config.noOfTerms" class="block w-full bg-white text-xs font-medium text-gray-900 transition-all hover:bg-[#F5F5F5] focus:bg-[#F5F5F5]">
                        <a :href="result.slug" class="flex gap-2 items-center px-3 py-3  ">
                                <template v-if="result.base_image">
                                    <img :src="result.base_image" alt="" class="h-12 w-12 rounded-md" />
                                </template>
                                <template v-else>
                                    <div class="icon-search flex items-center text-l"></div>
                                </template>

                                <p
                                    :class="config.display === 'ar' ? 'mr-1' : ''"
                                    class="overflow-hidden text-ellipsis whitespace-nowrap"
                                >
                                    <span v-html="result.name"></span>
                                </p>
                        </a>
                    </div>
                </span>
            </div>
        </div>
    </div>
</script>
<script type="module">
    app.component('v-suggestion-searchbar', {
        template: '#v-suggestion-searchbar-template',
        data() {
            return {
                term: '',
                category: '',
                isSearching: false,
                productResults: [],
                suggestsResults: [],
                highlightedResults: [],
                visibleProductsCount: 10,
                debounceTimer: null,
                currentController: null,
                config: {
                    displayProductToggle: "{{ core()->getConfigData('general.search.settings.display_product_toggle') }}",
                    noOfTerms: "{{ core()->getConfigData('general.search.settings.show_products') }}",
                    displayTermsToggle: "{{ core()->getConfigData('general.search.settings.display_terms_toggle') }}",
                    displayCategory: "{{ core()->getConfigData('general.search.settings.display_categories_toggle') }}",
                    minSearchTerms: "{{ core()->getConfigData('general.search.settings.min_search_terms') }}",
                    display: "{{ core()->getCurrentLocale()->code }}"
                },
            };
        },
        computed: {
            showMoreButton() {
                return this.visibleProductsCount < this.suggestsResults.length;
            }
        },
        methods: {
            search() {
                if (this.debounceTimer) {
                    clearTimeout(this.debounceTimer);
                }
                this.debounceTimer = setTimeout(() => {
                    if (this.term.length >= this.config.minSearchTerms) {
                        if (this.currentController) {
                            this.currentController.abort();
                        }
                        this.currentController = new AbortController();
                        this.isSearching = true;
                        this.$axios.get("{{ route('search_suggestion.search.index') }}", {
                            params: { term: this.term, category: this.category }
                        })
                            .then (response => {
                                this.handleResponse(response.data);
                            })
                            .catch (error => {
                                if (error.name === 'AbortError') return;
                                console.error("Search request failed:", error);
                            }).finally(() => {
                                this.isSearching = false;
                            });
                    } else {
                        this.suggestsResults = [];
                    }
                }, 300);
            },
            handleResponse(data) {
                console.log("Search results:", data);
                const escapeHtml = (unsafe) => {
                    return unsafe
                        .replace(/&/g, "&amp;")
                        .replace(/</g, "&lt;")
                        .replace(/>/g, "&gt;")
                        .replace(/"/g, "&quot;")
                        .replace(/'/g, "&#039;");
                };
                const searchTerm = this.term.toLowerCase();
                const searchTermReversed = searchTerm.split('').reverse().join('');
                const results = data.data;

                console.log('results', results);
                const formattedResults = results.map(result => {
                    const originalText = result.name.toLowerCase();
                    const index1 = originalText.indexOf(searchTerm);
                    const index2 = originalText.indexOf(searchTermReversed);
                    let formattedName = escapeHtml(result.name);
                    if (index1 !== -1 || index2 !== -1) {
                        const startIndex = index1 !== -1 ? index1 : index2;
                        const foundTerm = index1 !== -1 ? searchTerm : searchTermReversed;
                        const escapedName = escapeHtml(result.name);
                        formattedName = `${escapedName.slice(0, startIndex)}<span class="font-semibold">${escapedName.slice(startIndex, startIndex + foundTerm.length)}</span>${escapedName.slice(startIndex + foundTerm.length)}`;
                    }
                    return { ...result, name: formattedName };
                });
                this.suggestsResults = formattedResults;
                this.isSearching = false;
            },
            loadMoreResults() {
                this.visibleProductsCount += 10;
                this.updateDisplayedResults();
            },
            updateDisplayedResults() {
                this.productResults = this.suggestsResults.slice(0, this.visibleProductsCount);
            },
            focusInput(event) {
                $(event.target.parentElement.parentElement).find('input').focus();
                this.search();
            },
            submitForm() {
                if (this.term !== '') {
                    document.getElementsByName('term')[0].value = this.term;
                    document.getElementById('search-form').submit();
                }
            }
        },
        watch: {
            suggestsResults: {
                immediate: true,
                handler() {
                    this.updateDisplayedResults();
                }
            }
        }
    });
</script>
@endPushOnce
