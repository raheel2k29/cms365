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
            'csv_file' => 'required|file|max:10240' // 10MB max
        ]);

        $file = $request->file('csv_file');
        $vendorId = $request->vendor_id;
        $extension = strtolower($file->getClientOriginalExtension());
        
        $rows = [];
        if ($extension === 'xlsx') {
            if ($xlsx = \Shuchkin\SimpleXLSX::parse($file->getRealPath())) {
                $rows = $xlsx->rows();
            } else {
                return back()->with('error', \Shuchkin\SimpleXLSX::parseError());
            }
        } else {
            if (($handle = fopen($file->getRealPath(), "r")) !== FALSE) {
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $rows[] = $data;
                }
                fclose($handle);
            }
        }
        
        $headerIndexMap = [
            'item_number' => null,
            'description' => null,
            'price' => null,
            'cost' => null,
            'sell' => null,
            'category' => null,
            'unit' => null,
        ];
        
        $headerRowFound = false;
        $dataRows = [];
        
        foreach ($rows as $row) {
            if (empty(array_filter($row))) continue;
            
            if (!$headerRowFound) {
                $isHeader = false;
                foreach ($row as $index => $val) {
                    if ($val === null) continue;
                    $valLower = strtolower(trim($val));
                    
                    if (in_array($valLower, ['item', 'item number', 'item_number', 'sku', 'product number', 'product_number', 'part number', 'part_number', 'part #', 'model', 'model number'])) {
                        $headerIndexMap['item_number'] = $index;
                        $isHeader = true;
                    } elseif (in_array($valLower, ['description', 'desc', 'item description', 'product description', 'name'])) {
                        $headerIndexMap['description'] = $index;
                        $isHeader = true;
                    } elseif (in_array($valLower, ['price', 'sell price', 'sell_price', 'price each', 'sell', 'list price', 'retail price', 'price list'])) {
                        $headerIndexMap['price'] = $index;
                        $isHeader = true;
                    } elseif (in_array($valLower, ['cost', 'cost price', 'cost_price', 'net cost', 'dealer price', 'net price'])) {
                        $headerIndexMap['cost'] = $index;
                        $isHeader = true;
                    } elseif (in_array($valLower, ['category', 'catagory', 'group', 'class', 'type'])) {
                        $headerIndexMap['category'] = $index;
                        $isHeader = true;
                    } elseif (in_array($valLower, ['unit', 'uom', 'ea', 'unit of measure'])) {
                        $headerIndexMap['unit'] = $index;
                        $isHeader = true;
                    }
                }
                
                if ($isHeader) {
                    $headerRowFound = true;
                    continue;
                }
            }
            
            if ($headerRowFound) {
                $dataRows[] = $row;
            } else {
                $dataRows[] = $row;
            }
        }
        
        if (!$headerRowFound) {
            $headerIndexMap = [
                'item_number' => 0,
                'description' => 1,
                'cost' => 2,
                'sell' => 3,
                'unit' => 4,
                'price' => null,
                'category' => null,
            ];
        }
        
        $currentCategory = '';
        $count = 0;
        
        foreach ($dataRows as $data) {
            if (empty(array_filter($data))) continue;
            
            if ($headerIndexMap['category'] !== null && isset($data[$headerIndexMap['category']]) && trim($data[$headerIndexMap['category']]) !== '') {
                $currentCategory = trim($data[$headerIndexMap['category']]);
            }
            
            $itemNum = null;
            if ($headerIndexMap['item_number'] !== null && isset($data[$headerIndexMap['item_number']])) {
                $itemNum = trim($data[$headerIndexMap['item_number']]);
            }
            
            $desc = null;
            if ($headerIndexMap['description'] !== null && isset($data[$headerIndexMap['description']])) {
                $desc = trim($data[$headerIndexMap['description']]);
            }
            
            if ($itemNum && !$desc) {
                $desc = ($currentCategory ? $currentCategory . ' - ' : '') . $itemNum;
            }
            
            if (!$desc && !$itemNum) continue;
            
            $cost = 0;
            $sell = 0;
            
            if ($headerIndexMap['cost'] !== null && isset($data[$headerIndexMap['cost']])) {
                $cost = floatval(preg_replace('/[^0-9.]/', '', $data[$headerIndexMap['cost']]));
            }
            if ($headerIndexMap['sell'] !== null && isset($data[$headerIndexMap['sell']])) {
                $sell = floatval(preg_replace('/[^0-9.]/', '', $data[$headerIndexMap['sell']]));
            }
            
            if ($headerIndexMap['price'] !== null && isset($data[$headerIndexMap['price']])) {
                $price = floatval(preg_replace('/[^0-9.]/', '', $data[$headerIndexMap['price']]));
                if ($cost == 0) $cost = $price;
                if ($sell == 0) $sell = $price;
            }
            
            $unit = null;
            if ($headerIndexMap['unit'] !== null && isset($data[$headerIndexMap['unit']])) {
                $unit = trim($data[$headerIndexMap['unit']]);
            }
            
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

    public function destroy(VendorItem $item)
    {
        $item->delete();
        return back()->with('success', 'Catalog item deleted successfully.');
    }

    public function bulkDelete(Request $request)
    {
        if ($request->has('vendor_id') && $request->vendor_id) {
            $count = VendorItem::where('vendor_id', $request->vendor_id)->delete();
            return back()->with('success', "Successfully deleted {$count} catalog items for the selected vendor.");
        }
        
        $count = VendorItem::count();
        VendorItem::truncate();
        return back()->with('success', "Successfully deleted all {$count} catalog items from the database.");
    }
}
