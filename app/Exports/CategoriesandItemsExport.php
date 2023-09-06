<?php

namespace App\Exports;

use App\Models\AdditionalLanguage;
use App\Models\Languages;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CategoriesandItemsExport implements WithMultipleSheets
{
    protected $data;
    protected $shop_id;

    public function __construct($data,$shop_id)
    {
        $this->data = $data;
        $this->shop_id = $shop_id;
    }

    public function sheets(): array
    {
        $sheets = [];
        $lang_codes = [];
        $all_data = $this->data;

        // Primary Language Details
        $language_setting = clientLanguageSettings($this->shop_id);
        $primary_lang_id = isset($language_setting['primary_language']) ? $language_setting['primary_language'] : '';
        $primary_language_details = getLangDetails($primary_lang_id);
        $primary_lang_code = isset($primary_language_details->code) ? $primary_language_details->code : '';
        $lang_codes[$primary_lang_id] = $primary_lang_code;

        // Additional Languages
        $additional_languages = AdditionalLanguage::where('shop_id',$this->shop_id)->get();
        if(count($additional_languages) > 0)
        {
            foreach($additional_languages as $value)
            {
                // Additional Language Details
                $add_lang_detail = Languages::where('id',$value->language_id)->first();
                $add_lang_code = isset($add_lang_detail->code) ? $add_lang_detail->code : '';
                $add_lang_id = isset($add_lang_detail->id) ? $add_lang_detail->id : '';
                $lang_codes[$add_lang_id] = $add_lang_code;
            }
        }

        foreach($all_data['categories'] as $category)
        {
            $sheets[] = new SingleExport($category,$all_data['languages'],$lang_codes);
        }

        return $sheets;
    }

}
