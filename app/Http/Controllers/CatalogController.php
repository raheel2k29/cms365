<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\VendorItem;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $query = VendorItem::with('vendor');
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('item_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        $items = $query->paginate(50)->withQueryString();
        $vendors = Vendor::orderBy('name')->get();

        return view('catalog.index', compact('items', 'vendors'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'csv_file' => 'required|file|mimes:csv,txt|max:5120' // 5MB max
        ]);

        $file = $request->file('csv_file');
        $vendorId = $request->vendor_id;
        
        if (($handle = fopen($file->getRealPath(), "r")) !== FALSE) {
            $header = fgetcsv($handle, 1000, ","); // Skip header
            
            $count = 0;
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                // Assuming format: Item Number, Description, Cost, Sell, Unit
                if (!isset($data[1]) || trim($data[1]) === '') continue; // description is required

                $itemNum = isset($data[0]) ? trim($data[0]) : null;
                $desc = trim($data[1]);
                $cost = isset($data[2]) ? floatval(preg_replace('/[^0-9.]/', '', $data[2])) : 0;
                $sell = isset($data[3]) ? floatval(preg_replace('/[^0-9.]/', '', $data[3])) : 0;
                $unit = isset($data[4]) ? trim($data[4]) : null;

                // Create or update based on item number or description if item number is missing
                if ($itemNum) {
                    $item = VendorItem::firstOrNew([
                        'vendor_id' => $vendorId,
                        'item_number' => $itemNum
                    ]);
                } else {
                    $item = VendorItem::firstOrNew([
                        'vendor_id' => $vendorId,
                        'description' => $desc
                    ]);
                }

                $item->description = $desc;
                $item->cost_price = $cost;
                $item->sell_price = $sell;
                $item->unit = $unit;
                $item->save();
                
                $count++;
            }
            fclose($handle);
        }

        return back()->with('success', "Successfully imported {$count} items.");
    }

    public function search(Request $request)
    {
        $search = $request->get('q');
        
        if (strlen($search) < 2) {
            return response()->json([]);
        }

        $items = VendorItem::with('vendor')
            ->where('description', 'like', "%{$search}%")
            ->orWhere('item_number', 'like', "%{$search}%")
            ->limit(20)
            ->get();

        return response()->json($items);
    }
}
