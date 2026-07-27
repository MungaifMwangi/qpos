<?php

namespace App\Http\Controllers\Backend\Product;

use App\Exports\DemoProductsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Imports\ProductsImport;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Trait\FileHandler;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\DataTables;

class ProductController extends Controller
{
    public $fileHandler;

    public function __construct(FileHandler $fileHandler)
    {
        $this->fileHandler = $fileHandler;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        abort_if(!auth()->user()->can('product_view'), 403);
        if ($request->ajax()) {
            $products = Product::latest()->get();
            return DataTables::of($products)
                ->addIndexColumn()
                ->addColumn('image', fn($data) => '<img src="' . asset('storage/' . $data->image) . '" loading="lazy" alt="' . $data->name . '" class="prod-thumb" onerror="this.onerror=null; this.src=\'' . asset('assets/images/no-image.png') . '\';" />')
                ->addColumn('name', fn($data) => '<div style="font-weight:600;font-size:13px;color:#303030">' . $data->name . '</div><div style="font-size:11px;color:#999">' . $data->sku . '</div>')
                ->addColumn(
                    'price',
                    fn($data) => '<span style="font-weight:600;font-size:13px">' . number_format($data->discounted_price, 2) . '</span>' .
                        ($data->price > $data->discounted_price
                            ? '<br><del style="font-size:11px;color:#999">' . number_format($data->price, 2) . '</del>'
                            : '')
                )
                ->addColumn('quantity', fn($data) => '<span style="font-size:13px">' . $data->quantity . ' ' . optional($data->unit)->short_name . '</span>')
                ->addColumn('created_at', fn($data) => '<span style="font-size:12px;color:#666">' . $data->created_at->format('d M, Y') . '</span>')
                ->addColumn('status', fn($data) => $data->status
                    ? '<span class="prod-badge-active">Active</span>'
                    : '<span class="prod-badge-inactive">Inactive</span>')
                ->addColumn('action', function ($data) {
                    $editUrl = route('backend.admin.products.edit', $data->id);
                    $purchaseUrl = route('backend.admin.purchase.create', ['barcode' => $data->sku]);
                    return '<a href="' . $editUrl . '" class="prod-action-edit" title="Edit"><i class="fas fa-edit"></i></a> '
                         . '<a href="' . $purchaseUrl . '" class="prod-action-purchase" title="Purchase"><i class="fas fa-cart-plus"></i></a>';
                })
                ->rawColumns(['image', 'name', 'price', 'quantity', 'created_at', 'status', 'action'])
                ->toJson();
        }
        if ($request->wantsJson()) {
            $request->validate([
                'search' => 'required|string|max:255',
            ]);

            // Initialize the query
            $products = Product::query();

            // Apply filters based on the search term
            $products = $products->where(function ($query) use ($request) {
                $query->where('name', 'LIKE', "%{$request->search}%")
                    ->orWhere('sku', $request->search);
            });
            // Get the results
            $products = $products->get();
            // Return the results as a JSON response
            return ProductResource::collection($products);
        }
        return view('backend.products.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        abort_if(!auth()->user()->can('product_create'), 403);
        $brands = Brand::whereStatus(true)->get();
        $categories = Category::whereStatus(true)->get();
        $units = Unit::all();
        return view('backend.products.create', compact('brands', 'categories', 'units'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {

        abort_if(!auth()->user()->can('product_create'), 403);
        $validated = $request->validated();
        $product = Product::create($validated);
        if ($request->hasFile("product_image")) {
            $product->image = $this->fileHandler->fileUploadAndGetPath($request->file("product_image"), "/public/media/products");
            $product->save();
        }

        return redirect()->route('backend.admin.products.index')->with('success', 'Product created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {

        abort_if(!auth()->user()->can('product_update'), 403);

        $product = Product::findOrFail($id);
        $brands = Brand::whereStatus(true)->get();
        $categories = Category::whereStatus(true)->get();
        $units = Unit::all();
        return view('backend.products.edit', compact('brands', 'categories', 'units', 'product'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, $id)
    {

        abort_if(!auth()->user()->can('product_update'), 403);
        $validated = $request->validated();
        $product = Product::findOrFail($id);
        $oldImage = $product->image;
        $product->update($validated);
        if ($request->hasFile("product_image")) {
            $product->image = $this->fileHandler->fileUploadAndGetPath($request->file("product_image"), "/public/media/products");
            $product->save();
            $this->fileHandler->secureUnlink($oldImage);
        }

        return redirect()->route('backend.admin.products.index')->with('success', 'Product updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {

        abort_if(!auth()->user()->can('product_delete'), 403);
        $product = Product::findOrFail($id);
        if ($product->image != '') {
            $this->fileHandler->secureUnlink($product->image);
        }
        $product->delete();
        return redirect()->back()->with('success', 'Product Deleted Successfully');
    }
    public function import(Request $request)
    {
        abort_if(!auth()->user()->can('product_import'), 403);

        if ($request->query('download-demo')) {
            return Excel::download(new DemoProductsExport, 'demo_products.xlsx');
        }

        if ($request->isMethod('post') && $request->hasFile('file')) {
            try {
                Excel::import(new ProductsImport, $request->file('file'));
                return redirect()->route('backend.admin.products.index')->with('success', 'Products imported successfully!');
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
            }
        }

        return view('backend.products.import');
    }
}
