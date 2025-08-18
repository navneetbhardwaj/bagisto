<?php

namespace Webkul\Shop\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use Webkul\Product\Repositories\ProductRepository;

class SuggestionController extends Controller
{
    use DispatchesJobs, ValidatesRequests;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(protected ProductRepository $productRepository) {}

    /**
     * Handle the search results
     *
     * @return array
     */
    public function search()
    {
        $params = request()->input();

        $term = $params['term'] ?? '';

        $results = $this->productRepository->setSearchEngine(core()->getConfigData('catalog.products.search.engine'))->searchProductByTerm($term);

        return response()->json($results);
    }
}
