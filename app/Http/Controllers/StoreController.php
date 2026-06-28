<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Store;
use App\Models\StoreOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StoreController extends Controller
{
    public function index()
    {
        $stores = Store::active()->latest()->paginate(12);

        return view('stores.index', [
            'stores' => $stores,
            'seo' => [
                'title' => 'متاجر وريد',
                'description' => 'تصفّح المتاجر الإلكترونية التي أُنشئت عبر منصة وريد.',
            ],
        ]);
    }

    public function show(Store $store)
    {
        abort_unless($store->status === 'active', 404);
        app()->instance('currentStoreId', $store->id);

        $categories = $store->categories()->where('is_active', true)->orderBy('sort_order')->get();
        $products = $store->products()->where('is_active', true)->orderBy('sort_order')->paginate(12);

        return view('stores.show', [
            'store' => $store,
            'categories' => $categories,
            'products' => $products,
            'seo' => $this->storeSeo($store),
        ]);
    }

    public function product(Store $store, Product $product)
    {
        abort_unless($store->status === 'active' && $product->is_active, 404);
        app()->instance('currentStoreId', $store->id);

        return view('stores.product', [
            'store' => $store,
            'product' => $product,
            'seo' => [
                'title' => $product->meta_title ?: ($product->name.' — '.$store->name),
                'description' => $product->meta_description ?: Str::limit(strip_tags((string) $product->description), 160),
                'image' => $product->firstImageUrl(),
                'og_type' => 'product',
            ],
        ]);
    }

    public function order(Request $request, Store $store, Product $product)
    {
        abort_unless($store->status === 'active' && $product->is_active, 404);
        app()->instance('currentStoreId', $store->id);

        $data = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:30',
            'customer_email' => 'nullable|email|max:255',
            'governorate' => 'nullable|string|max:100',
            'shipping_address' => 'required|string|max:1000',
            'quantity' => 'nullable|integer|min:1|max:99',
            'notes' => 'nullable|string|max:1000',
        ]);

        $qty = max(1, (int) ($data['quantity'] ?? 1));
        $subtotal = $product->price * $qty;                    // قروش (Snapshot)
        $shipping = (int) data_get($store->settings, 'shipping_fee', 0);
        $total = $subtotal + $shipping;

        $order = StoreOrder::create([
            'store_id' => $store->id,
            'order_number' => 'WRD-'.strtoupper(Str::random(8)),
            'customer_name' => $data['customer_name'],
            'customer_phone' => $data['customer_phone'],
            'customer_email' => $data['customer_email'] ?? null,
            'shipping_address' => $data['shipping_address'],
            'governorate' => $data['governorate'] ?? null,
            'items' => [[                                       // لقطة محاسبية — دستور §3
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => $qty,
                'line_total' => $subtotal,
            ]],
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total' => $total,
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'status' => 'new',
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()
            ->route('store.show', $store)
            ->with('success', 'تم استلام طلبك رقم '.$order->order_number.' بنجاح! سنتواصل معك لتأكيد الطلب والدفع عند الاستلام.');
    }

    private function storeSeo(Store $store): array
    {
        return [
            'title' => $store->meta_title ?: $store->name,
            'description' => $store->meta_description ?: $store->tagline ?: ('متجر '.$store->name.' على منصة وريد'),
            'image' => $store->logoUrl(),
        ];
    }
}
