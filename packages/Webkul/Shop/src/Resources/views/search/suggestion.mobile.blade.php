<v-suggestion-searchbar-mobile></v-suggestion-searchbar-mobile>

@pushOnce('scripts')

    <script type="text/x-template" id="v-suggestion-searchbar-mobile-template">
        <form
            action="{{ route('shop.search.index') }}"
            class="mb-4 flex w-full items-center"
        >

            <label
                for="organic-search"
                class="sr-only"
            >
                Search
                Search
            </label>
            <div class="relative w-full">
                <div class="icon-search pointer-events-none absolute left-3 top-3 flex items-center text-[25px]">
                </div>
                <input
                    type="text"
                    name="query"
                    value="{{ request('query') }}"

                    class="block w-full rounded-xl border border-['#E3E3E3'] px-11 py-3.5 text-xs font-medium text-gray-900"

                    placeholder="@lang('shop::app.components.layouts.header.desktop.bottom.search-text')"

                    aria-label="@lang('shop::app.components.layouts.header.desktop.bottom.search-text')"

                    aria-required="true"

                    v-model="term"

                    autocomplete="off"

                        onblur="setTimeout(function() {if(document.querySelector('#suggest_m')){document.querySelector('#suggest_m').classList.add('hidden')}}, 300)"

                        onfocus="if(document.querySelector('#suggest_m'))document.querySelector('#suggest_m').classList.remove('hidden')"

                    @keyup="search()"

                    required

                >

                <button

                    type="button"

                    class="icon-camera absolute right-3 top-3 flex items-center pr-3 text-[22px]"

                    aria-label="Search"

                    id="header-search-icon"

                    @click="submitForm"

                >

                </button>



                <div

                    class="absolute z-10 max-h-96 w-full overflow-auto rounded border" id="suggest_m"

                    v-if="term.length >= config.minSearchTerms"

                >

                    <div

                        :class="config.display === 'ar' ? 'ar' : ''"

                        v-if="suggestsResults.length"

                    >

                        <span

                            v-for="(result, index) in suggestsResults"

                        >

                            <div v-if="index < config.noOfTerms">

                                <a :href="result.url_key">

                                    <div class="h-8 border border-blue-100 bg-white p-2 text-sm hover:border-red-100 hover:bg-gray-200">

                                        <p

                                            :class="config.display === 'ar' ? 'mr-1' : ''"

                                            class="overflow-hidden text-ellipsis whitespace-nowrap"

                                        >

                                            <span v-html="result.name"></span>



                                            @if (core()->getConfigData('general.search.settings.display_categories_toggle'))

                                                <span v-if="result?.categories?.length">

                                                    in

                                                    <span

                                                        class="font-semibold"

                                                        v-for="(category, index) in result.categories"

                                                    >

                                                        <template v-if="index < result.categories.length - 1">

                                                            @{{ category.name }},

                                                        </template>



                                                        <template v-else>

                                                            @{{ category.name }}

                                                        </template>

                                                    </span>

                                                </span>

                                            @endif

                                        </p>

                                    </div>

                                </a>

                            </div>

                        </span>



                        @if(core()->getConfigData('general.search.settings.display_terms_toggle'))

                            <a :href="'search?query=' + term + '&sort=price-desc&limit=12&mode=grid'">

                                <div class="h-9 border border-blue-100 bg-white p-2 hover:border-red-100 hover:bg-gray-200">

                                    <div v-if="config.display === 'ar'">

                                        @{{ term }}



                                        <span class="float-left">

                                            @{{ suggestsResults.length }}

                                        </span>

                                    </div>



                                    <p v-else>

                                        @{{ term }}



                                        <span class="float-right ltr:mr-1 rtl:ml-1">

                                            @{{ suggestsResults.length }}

                                        </span>

                                    </p>

                                </div>

                            </a>

                        @endif



                        @if(core()->getConfigData('general.search.settings.display_product_toggle'))

                            <div class="h-9 border bg-blue-700 p-2 text-center font-bold text-blue-200">

                                <p>

                                    Popular Products

                                </p>

                            </div>

                            <a

                                :href="result.url_key"

                                v-for="(result, index) in productResults"

                            >

                                <div class="flex w-full border border-blue-100 bg-white hover:border-red-100 hover:bg-gray-200">

                                    <div class="w-1/4">

                                        <img

                                            class="max-h-20 min-h-20 min-w-20 max-w-20 rounded-full p-2"

                                            v-if="result.images.length"

                                            :src="result.images[0].url"

                                        />

                                    </div>



                                    <div class="w-3/4 p-1">

                                        <div

                                            class="m-4 overflow-hidden text-ellipsis whitespace-nowrap"

                                            :class="config.display === 'ar' ? 'mr-2' : ''"

                                        >

                                            <span v-html="result.name"></span>

                                            <br>

                                            <div

                                                class="product-price flex gap-3"

                                                v-html="result.price_html"

                                            >

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </a>

                            <a

                                href="javascript:void(0)"

                                class="show-more-btn"

                                v-if="showMoreButton"

                                @click="loadMoreResults"

                            >

                                Show More

                            </a>

                        @endif

                    </div>



                    <div

                        class="h-10 border bg-white p-2"

                        :class="config.display === 'ar' ? 'ar' : ''"

                        v-if="isSearching"

                    >

                        <p>

                            Searching...

                        </p>

                    </div>

                    <div

                        class="h-10 border bg-white p-2"

                        :class="config.display === 'ar' ? 'ar' : ''"

                        v-if="! isSearching && ! suggestsResults.length"

                    >

                        <p>

                            No results found.

                        </p>

                    </div>

                </div>

            </div>

        </form>

    </script>



    <script type="module">

        app.component('v-suggestion-searchbar-mobile', {

            template: '#v-suggestion-searchbar-mobile-template',



            data() {

                return {

                    term: '',



                    category: '',



                    isSearching: false,



                    productResults: [],



                    suggestsResults: [],



                    highlightedResults: [],



                    visibleProductsCount: 10,



                    config: {

                        displayProductToggle: "{{ core()->getConfigData('general.search.settings.display_product_toggle') }}",



                        noOfTerms: "{{ core()->getConfigData('general.search.settings.show_products') }}",



                        displayTermsToggle: "{{ core()->getConfigData('general.search.settings.display_terms_toggle') }}",



                        displayCategory: "{{ core()->getConfigData('general.search.settings.display_categories_toggle') }}",



                        minSearchTerms: "{{ core()->getConfigData('general.search.settings.min_search_terms') }}",



                        display: "{{ core()->getCurrentLocale()->code }}"

                    }

                }

            },



            computed: {

                showMoreButton() {

                    return this.visibleProductsCount < this.suggestsResults.length;

                }

            },



            methods: {

                search() {

                    if (this.term.length >= this.config.minSearchTerms) {

                        this.isSearching = true;



                        this.$axios.get("{{ route('search_suggestion.search.index') }}", {

                            params: { term: this.term, category: this.category }

                        })

                            .then(response => {

                                this.handleResponse(response.data);

                            })

                            .catch (error => {

                                console.error("Error:", error);

                            })

                    } else {

                        this.suggestsResults = [];

                    }

                },



                handleResponse(data) {

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
