<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Branch;
use App\Models\ProductBatch;
use App\Models\StockMovement;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentDetail;
use App\Models\StockTransfer;
use App\Models\StockTransferDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockManagementController extends Controller
{
    // 1. Dashboard / Overview Stok
    public function index()
    {
        $totalProducts = Product::count();
        $totalStockQty = Product::sum('stock');
        $totalStockValue = Product::selectRaw('SUM(stock * purchase_price) as val')->value('val') ?? 0;
        
        $lowStockProducts = Product::with('category')->whereColumn('stock', '<=', 'min_stock')->get();
        
        $today = now()->toDateString();
        $expiredBatches = ProductBatch::with('product')
            ->where('stock', '>', 0)
            ->whereNotNull('expired_date')
            ->where('expired_date', '<=', $today)
            ->get();

        $nearExpiryBatches = ProductBatch::with('product')
            ->where('stock', '>', 0)
            ->whereNotNull('expired_date')
            ->whereBetween('expired_date', [$today, now()->addDays(30)->toDateString()])
            ->get();

        $recentMovements = StockMovement::with(['product', 'user', 'branch'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('stock.index', compact(
            'totalProducts',
            'totalStockQty',
            'totalStockValue',
            'lowStockProducts',
            'expiredBatches',
            'nearExpiryBatches',
            'recentMovements'
        ));
    }

    // 2. Stok Masuk (Stock In)
    public function stockIn()
    {
        $products = Product::orderBy('name', 'asc')->get();
        $suppliers = Supplier::orderBy('name', 'asc')->get();
        $branches = Branch::orderBy('name', 'asc')->get();
        $movements = StockMovement::with(['product', 'user', 'branch'])
            ->where('type', 'in')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('stock.in', compact('products', 'suppliers', 'branches', 'movements'));
    }

    public function storeStockIn(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'qty' => 'required|integer|min:1',
            'purchase_price' => 'required|numeric|min:0',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'branch_id' => 'nullable|exists:branches,id',
            'batch_number' => 'nullable|string|max:100',
            'expired_date' => 'nullable|date',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $product = Product::findOrFail($request->product_id);
            $stockBefore = $product->stock;
            $stockAfter = $stockBefore + $request->qty;

            // Create batch record if batch or expiry specified
            $batch = null;
            if ($request->filled('batch_number') || $request->filled('expired_date')) {
                $batch = ProductBatch::create([
                    'product_id' => $product->id,
                    'batch_number' => $request->batch_number ?: 'BATCH-' . date('YmdHis'),
                    'stock' => $request->qty,
                    'expired_date' => $request->expired_date,
                    'purchase_price' => $request->purchase_price,
                ]);
            }

            // Update product stock and purchase price
            $product->stock = $stockAfter;
            if ($request->purchase_price > 0) {
                $product->purchase_price = $request->purchase_price;
            }
            $product->save();

            // Record Stock Movement
            StockMovement::create([
                'product_id' => $product->id,
                'product_batch_id' => $batch ? $batch->id : null,
                'branch_id' => $request->branch_id ?: Branch::where('is_main', true)->value('id'),
                'user_id' => auth()->id(),
                'type' => 'in',
                'qty' => $request->qty,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reference_number' => $request->reference_number ?: ('IN-' . date('YmdHis')),
                'reason' => 'Pembelian / Stok Masuk Supplier',
                'notes' => $request->notes,
            ]);

            DB::commit();
            return redirect()->route('stock.in')->with('success', "Stok Masuk untuk '{$product->name}' (+{$request->qty} {$product->unit}) berhasil dicatat!");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal mencatat Stok Masuk: ' . $e->getMessage())->withInput();
        }
    }

    // 3. Stok Keluar (Stock Out / Waste)
    public function stockOut()
    {
        $products = Product::where('stock', '>', 0)->orderBy('name', 'asc')->get();
        $branches = Branch::orderBy('name', 'asc')->get();
        $movements = StockMovement::with(['product', 'user', 'branch'])
            ->where('type', 'out')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('stock.out', compact('products', 'branches', 'movements'));
    }

    public function storeStockOut(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'qty' => 'required|integer|min:1',
            'reason' => 'required|string|max:255',
            'branch_id' => 'nullable|exists:branches,id',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $product = Product::findOrFail($request->product_id);

            if ($product->stock < $request->qty) {
                return redirect()->back()->with('error', "Stok tidak mencukupi! Sisa stok '{$product->name}' adalah {$product->stock}.")->withInput();
            }

            $stockBefore = $product->stock;
            $stockAfter = $stockBefore - $request->qty;

            // Decrement product stock
            $product->stock = $stockAfter;
            $product->save();

            // Record Stock Movement
            StockMovement::create([
                'product_id' => $product->id,
                'branch_id' => $request->branch_id ?: Branch::where('is_main', true)->value('id'),
                'user_id' => auth()->id(),
                'type' => 'out',
                'qty' => -$request->qty,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reference_number' => $request->reference_number ?: ('OUT-' . date('YmdHis')),
                'reason' => $request->reason,
                'notes' => $request->notes,
            ]);

            DB::commit();
            return redirect()->route('stock.out')->with('success', "Stok Keluar untuk '{$product->name}' (-{$request->qty} {$product->unit}) berhasil dicatat!");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal mencatat Stok Keluar: ' . $e->getMessage())->withInput();
        }
    }

    // 4. Penyesuaian Stok (Stock Opname)
    public function opname()
    {
        $products = Product::with('category')->orderBy('name', 'asc')->get();
        $adjustments = StockAdjustment::with(['user', 'details.product'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('stock.opname', compact('products', 'adjustments'));
    }

    public function storeOpname(Request $request)
    {
        $request->validate([
            'adjustment_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.physical_stock' => 'required|integer|min:0',
            'items.*.reason' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $adjNumber = 'OPN-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

            $adjustment = StockAdjustment::create([
                'adjustment_number' => $adjNumber,
                'user_id' => auth()->id(),
                'adjustment_date' => $request->adjustment_date,
                'notes' => $request->notes,
            ]);

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $systemStock = $product->stock;
                $physicalStock = (int)$item['physical_stock'];
                $diff = $physicalStock - $systemStock;

                if ($diff != 0) {
                    // Record detail
                    StockAdjustmentDetail::create([
                        'stock_adjustment_id' => $adjustment->id,
                        'product_id' => $product->id,
                        'system_stock' => $systemStock,
                        'physical_stock' => $physicalStock,
                        'difference_qty' => $diff,
                        'reason' => $item['reason'] ?? 'Rekonsiliasi Stock Opname',
                    ]);

                    // Update product stock to physical count
                    $product->stock = $physicalStock;
                    $product->save();

                    // Log Movement
                    StockMovement::create([
                        'product_id' => $product->id,
                        'user_id' => auth()->id(),
                        'type' => 'opname',
                        'qty' => $diff,
                        'stock_before' => $systemStock,
                        'stock_after' => $physicalStock,
                        'reference_number' => $adjNumber,
                        'reason' => $item['reason'] ?? 'Penyesuaian Stock Opname',
                        'notes' => "Selisih: " . ($diff > 0 ? "+{$diff}" : $diff),
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('stock.opname')->with('success', "Stock Opname #{$adjNumber} berhasil disimpan dan stok telah diperbarui!");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan Stock Opname: ' . $e->getMessage())->withInput();
        }
    }

    // 5. Transfer Stok Antar Cabang
    public function transfers()
    {
        $products = Product::where('stock', '>', 0)->orderBy('name', 'asc')->get();
        $branches = Branch::orderBy('name', 'asc')->get();
        $transfers = StockTransfer::with(['fromBranch', 'toBranch', 'sender', 'receiver', 'details.product'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('stock.transfers', compact('products', 'branches', 'transfers'));
    }

    public function storeTransfer(Request $request)
    {
        $request->validate([
            'from_branch_id' => 'required|exists:branches,id',
            'to_branch_id' => 'required|exists:branches,id|different:from_branch_id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $transferNumber = 'TRF-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

            $transfer = StockTransfer::create([
                'transfer_number' => $transferNumber,
                'from_branch_id' => $request->from_branch_id,
                'to_branch_id' => $request->to_branch_id,
                'user_id' => auth()->id(),
                'status' => 'shipped',
                'notes' => $request->notes,
            ]);

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);

                if ($product->stock < $item['qty']) {
                    DB::rollBack();
                    return redirect()->back()->with('error', "Stok produk '{$product->name}' tidak mencukupi untuk ditransfer! (Sisa: {$product->stock})")->withInput();
                }

                StockTransferDetail::create([
                    'stock_transfer_id' => $transfer->id,
                    'product_id' => $product->id,
                    'qty' => $item['qty'],
                ]);

                // Reduce stock from origin
                $stockBefore = $product->stock;
                $stockAfter = $stockBefore - $item['qty'];
                $product->stock = $stockAfter;
                $product->save();

                // Log movement
                StockMovement::create([
                    'product_id' => $product->id,
                    'branch_id' => $request->from_branch_id,
                    'user_id' => auth()->id(),
                    'type' => 'transfer',
                    'qty' => -$item['qty'],
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'reference_number' => $transferNumber,
                    'reason' => "Transfer Keluar ke Cabang ID: {$request->to_branch_id}",
                    'notes' => $request->notes,
                ]);
            }

            DB::commit();
            return redirect()->route('stock.transfers')->with('success', "Transfer Stok #{$transferNumber} berhasil dikirim!");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memproses Transfer Stok: ' . $e->getMessage())->withInput();
        }
    }

    public function receiveTransfer(Request $request, StockTransfer $transfer)
    {
        if ($transfer->status === 'received') {
            return redirect()->back()->with('error', 'Transfer stok ini sudah diterima sebelumnya.');
        }

        DB::beginTransaction();
        try {
            $transfer->status = 'received';
            $transfer->received_by = auth()->id();
            $transfer->save();

            foreach ($transfer->details as $detail) {
                $product = $detail->product;
                $stockBefore = $product->stock;
                $stockAfter = $stockBefore + $detail->qty;

                $product->stock = $stockAfter;
                $product->save();

                StockMovement::create([
                    'product_id' => $product->id,
                    'branch_id' => $transfer->to_branch_id,
                    'user_id' => auth()->id(),
                    'type' => 'transfer',
                    'qty' => $detail->qty,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'reference_number' => $transfer->transfer_number,
                    'reason' => "Penerimaan Transfer dari Cabang ID: {$transfer->from_branch_id}",
                    'notes' => "Diterima oleh " . auth()->user()->name,
                ]);
            }

            DB::commit();
            return redirect()->route('stock.transfers')->with('success', "Penerimaan Transfer #{$transfer->transfer_number} berhasil dikonfirmasi!");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal mengonfirmasi penerimaan: ' . $e->getMessage());
        }
    }

    // 6. Riwayat Pergerakan Stok (Audit Log / Kartu Stok)
    public function history(Request $request)
    {
        $query = StockMovement::with(['product.category', 'user', 'branch']);

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('type') && $request->type != 'all') {
            $query->where('type', $request->type);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59']);
        }

        $movements = $query->orderBy('created_at', 'desc')->paginate(20);
        $products = Product::orderBy('name', 'asc')->get();

        return view('stock.history', compact('movements', 'products'));
    }

    // 7. Notifikasi Stok Minimum & Produk Expired
    public function alerts()
    {
        $today = now()->toDateString();
        
        $lowStockProducts = Product::with('category')
            ->whereColumn('stock', '<=', 'min_stock')
            ->orderBy('stock', 'asc')
            ->get();

        $expiredBatches = ProductBatch::with('product')
            ->where('stock', '>', 0)
            ->whereNotNull('expired_date')
            ->where('expired_date', '<=', $today)
            ->orderBy('expired_date', 'asc')
            ->get();

        $nearExpiryBatches = ProductBatch::with('product')
            ->where('stock', '>', 0)
            ->whereNotNull('expired_date')
            ->whereBetween('expired_date', [$today, now()->addDays(60)->toDateString()])
            ->orderBy('expired_date', 'asc')
            ->get();

        return view('stock.alerts', compact('lowStockProducts', 'expiredBatches', 'nearExpiryBatches'));
    }

    // Quick Supplier Creator Modal
    public function storeSupplier(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
        ]);

        $supplier = Supplier::create($request->all());

        return response()->json([
            'success' => true,
            'message' => "Supplier '{$supplier->name}' berhasil ditambahkan!",
            'supplier' => $supplier,
        ]);
    }
}
