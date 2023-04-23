<?php

namespace App\Http\Controllers;

use App\Models\Appearance;
use App\Models\Brand;
use App\Models\Business;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        //分类
        $category = getTree(Category::getProductCategory());
        //品牌
        $brand = Brand::getNavBrand();
        //外观
        $appearance = Appearance::getProductList();

        //产品
        $query = Product::query();
        $categoryIds = [];
        $brandIds = [];
        $appearanceIds = [];
        if ($request->filled('category')) {
            $categoryInfo = Category::find($request->input('category'));
            if ($categoryInfo->parent_id == 0) {
                $categoryList = Category::where('parent_id', '=', $categoryInfo->id)->get(['id'])->toArray();
                if (!empty($categoryList)) {
                    $categoryIds = [$categoryList[0]['id']];
                }
            } else {
                $categoryIds = [$request->input('category')];
            }

            $query->whereIn('category_id', $categoryIds);
        }
        if ($request->filled('brand')) {
            $brandIds = explode(',', $request->input('brand'));
            $query->whereIn('brand_id', $brandIds);
        }
        if ($request->filled('appearance')) {
            $appearanceIds = explode(',', $request->input('appearance'));
            $query->whereIn('appearance_id', $appearanceIds);
        }

        $product = $query->paginate(16);
        return view('product_index', [
            'categoryList'  => $category,
            'brand'         => $brand,
            'appearances'   => $appearance,
            'list'          => $product,
            'categoryIds'   => $categoryIds,
            'brandIds'      => $brandIds,
            'appearanceIds'      => $appearanceIds
        ]);
    }

    public function info($id)
    {
        $info = Product::find($id);
        if (empty($info)) {
            return redirect('/');
        }

        //推荐商品
        $data = DB::table('product')
            ->where('category_id', '=', $info->category_id)
            ->limit(5)
            ->get()
            ->toArray();

        $info->attribute = @json_decode($info->attribute);
        $info->slide = @json_decode($info->slide);
        return view('product_info', [
            'info' => $info,
            'recommend' => $data
        ]);
    }
}
