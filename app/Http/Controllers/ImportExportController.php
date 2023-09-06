<?php

namespace App\Http\Controllers;

use App\Exports\CategoriesandItemsExport;
use App\Imports\CategoryandItemsImport;
use App\Models\Category;
use App\Models\Items;
use App\Models\Languages;
use App\Models\Shop;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ImportExportController extends Controller
{
    public function index()
    {
        $data['shops'] = Shop::get();
        return view('admin.import_export.import_export',$data);
    }

    public function importData(Request $request)
    {
        $request->validate([
            'shop' => 'required',
            'import' => 'required|mimes:xls,csv,xlsx',
        ]);

        $count_records = 0;
        $count_records += Category::where('shop_id',$request->shop)->count();
        $count_records += Items::where('shop_id',$request->shop)->count();

        if($count_records > 0)
        {
            return redirect()->route('admin.import.export')->with('error','Please Delete Old Records to Insert New Record');
        }

        Excel::import(new CategoryandItemsImport($request->shop),$request->file('import'));

        return redirect()->back();
    }


    public function exportData(Request $request)
    {
        $shop_id = $request->shop_id;

        if ($shop_id)
        {
            $data['languages'] = Languages::get();
            $data['categories'] = Category::where('shop_id',$shop_id)->get();
            $data['items'] = Items::where('shop_id',$shop_id)->get();

            $all_data[] = $data;

            if((count($data['languages']) > 0) && (count($data['categories']) > 0) && (count($data['items']) > 0))
            {
                try
                {
                    return Excel::download(new CategoriesandItemsExport($data,$shop_id),'shop_data.xlsx');
                }
                catch (\Throwable $th)
                {
                    return redirect()->back()->with('error','Something Went Wrong!');
                }
            }
        }
        else
        {
            return redirect()->back()->with('error','Shop Does not Exists');
        }
    }
}
