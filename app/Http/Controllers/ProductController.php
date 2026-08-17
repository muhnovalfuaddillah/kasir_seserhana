<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'wholesalePrices']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Tampilkan stok yang menipis / habis di paling atas
        $products = $query->orderBy('stock', 'asc')->orderBy('name', 'asc')->paginate(12);
        $categories = Category::all();

        return view('products.index', compact('products', 'categories'));
    }

    private function cleanCurrency($value)
    {
        if (is_null($value) || $value === '') return 0;
        if (is_numeric($value)) return (float)$value;
        $clean = preg_replace('/[^\d]/', '', (string)$value);
        return (float)($clean ?: 0);
    }

    public function store(Request $request)
    {
        // Sanitize currency inputs (remove thousands separator dots)
        $purchasePrice = $this->cleanCurrency($request->purchase_price);
        $sellingPrice = $this->cleanCurrency($request->selling_price);

        $wholesalePrices = [];
        if ($request->has('wholesale_price') && is_array($request->wholesale_price)) {
            foreach ($request->wholesale_price as $idx => $val) {
                $wholesalePrices[$idx] = $this->cleanCurrency($val);
            }
        }

        $cleanMinQty = [];
        $cleanLabels = [];
        $cleanPrices = [];

        if ($request->has('wholesale_min_qty') && is_array($request->wholesale_min_qty)) {
            foreach ($request->wholesale_min_qty as $idx => $val) {
                $pVal = $wholesalePrices[$idx] ?? 0;
                if (!is_null($val) && $val !== '' && (int)$val >= 1 && $pVal > 0) {
                    $cleanMinQty[] = (int)$val;
                    $cleanLabels[] = $request->wholesale_unit_label[$idx] ?? null;
                    $cleanPrices[] = $pVal;
                }
            }
        }

        $request->merge([
            'purchase_price' => $purchasePrice,
            'selling_price' => $sellingPrice,
            'wholesale_min_qty' => $cleanMinQty,
            'wholesale_unit_label' => $cleanLabels,
            'wholesale_price' => $cleanPrices,
            'barcode' => (!$request->filled('barcode') || trim($request->barcode) === '') ? 'BRC-' . strtoupper(Str::random(8)) : trim($request->barcode)
        ]);

        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'barcode' => 'required|string|unique:products,barcode',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'unit' => 'required|string|max:50',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'wholesale_min_qty' => 'nullable|array',
            'wholesale_min_qty.*' => 'nullable|integer|min:1',
            'wholesale_unit_label' => 'nullable|array',
            'wholesale_price' => 'nullable|array',
            'wholesale_price.*' => 'nullable|numeric|min:0',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $product = Product::create([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'barcode' => $request->barcode,
            'purchase_price' => $request->purchase_price,
            'selling_price' => $request->selling_price,
            'stock' => $request->stock,
            'unit' => $request->unit,
            'image' => $imagePath,
        ]);

        // Process Wholesale Tier Prices
        if ($request->filled('wholesale_min_qty') && is_array($request->wholesale_min_qty)) {
            foreach ($request->wholesale_min_qty as $index => $minQty) {
                if ($minQty > 0 && isset($request->wholesale_price[$index]) && $request->wholesale_price[$index] > 0) {
                    \App\Models\ProductWholesalePrice::create([
                        'product_id' => $product->id,
                        'min_qty' => $minQty,
                        'unit_label' => $request->wholesale_unit_label[$index] ?? "Min {$minQty} {$product->unit}",
                        'price' => $request->wholesale_price[$index],
                    ]);
                }
            }
        }

        return redirect()->back()->with('success', 'Produk dan Tier Harga Grosir berhasil ditambahkan!');
    }

    public function update(Request $request, Product $product)
    {
        // Sanitize currency inputs (remove thousands separator dots)
        $purchasePrice = $this->cleanCurrency($request->purchase_price);
        $sellingPrice = $this->cleanCurrency($request->selling_price);

        $wholesalePrices = [];
        if ($request->has('wholesale_price') && is_array($request->wholesale_price)) {
            foreach ($request->wholesale_price as $idx => $val) {
                $wholesalePrices[$idx] = $this->cleanCurrency($val);
            }
        }

        $cleanMinQty = [];
        $cleanLabels = [];
        $cleanPrices = [];

        if ($request->has('wholesale_min_qty') && is_array($request->wholesale_min_qty)) {
            foreach ($request->wholesale_min_qty as $idx => $val) {
                $pVal = $wholesalePrices[$idx] ?? 0;
                if (!is_null($val) && $val !== '' && (int)$val >= 1 && $pVal > 0) {
                    $cleanMinQty[] = (int)$val;
                    $cleanLabels[] = $request->wholesale_unit_label[$idx] ?? null;
                    $cleanPrices[] = $pVal;
                }
            }
        }

        $request->merge([
            'purchase_price' => $purchasePrice,
            'selling_price' => $sellingPrice,
            'wholesale_min_qty' => $cleanMinQty,
            'wholesale_unit_label' => $cleanLabels,
            'wholesale_price' => $cleanPrices,
        ]);

        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'barcode' => 'nullable|string|unique:products,barcode,' . $product->id,
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'unit' => 'required|string|max:50',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'wholesale_min_qty' => 'nullable|array',
            'wholesale_min_qty.*' => 'nullable|integer|min:1',
            'wholesale_unit_label' => 'nullable|array',
            'wholesale_price' => 'nullable|array',
            'wholesale_price.*' => 'nullable|numeric|min:0',
        ]);

        $data = [
            'category_id' => $request->category_id,
            'name' => $request->name,
            'barcode' => $request->barcode ?? $product->barcode,
            'purchase_price' => $request->purchase_price,
            'selling_price' => $request->selling_price,
            'stock' => $request->stock,
            'unit' => $request->unit,
        ];

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        // Re-sync Wholesale Prices
        $product->wholesalePrices()->delete();
        if ($request->filled('wholesale_min_qty') && is_array($request->wholesale_min_qty)) {
            foreach ($request->wholesale_min_qty as $index => $minQty) {
                if ($minQty > 0 && isset($request->wholesale_price[$index]) && $request->wholesale_price[$index] > 0) {
                    \App\Models\ProductWholesalePrice::create([
                        'product_id' => $product->id,
                        'min_qty' => $minQty,
                        'unit_label' => $request->wholesale_unit_label[$index] ?? "Min {$minQty} {$product->unit}",
                        'price' => $request->wholesale_price[$index],
                    ]);
                }
            }
        }

        return redirect()->back()->with('success', 'Produk & Harga Grosir berhasil diperbarui!');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->back()->with('success', 'Produk berhasil dihapus!');
    }
}
