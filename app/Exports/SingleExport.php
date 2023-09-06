<?php

namespace App\Exports;

use App\Models\Category;
use App\Models\CategoryProductTags;
use App\Models\ItemPrice;
use App\Models\Items;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;

class SingleExport implements FromCollection, WithTitle, WithHeadings, WithEvents
{
    private $category;
    private $languages;
    private $client_langs;

    public function __construct($category,$languages,$client_langs)
    {
        $this->category = $category;
        $this->languages = $languages;
        $this->client_langs = $client_langs;
    }

    // Export Category With Items
    public function collection()
    {
        $all_lang = $this->languages;
        $cat_dt = $this->category;
        $data = [];
        $all_excel_data = [];
        $lang_arr = [];
        $cat_arr = [];
        $parent_cat_name = '';


        // Insert Category Details
        $cat_type = (isset($this->category['category_type']) && !empty($this->category['category_type'])) ? $this->category['category_type'] : "";
        $is_parent = (isset($this->category['parent_category']) && !empty($this->category['parent_category'])) ? $this->category['parent_category'] : 0;
        $link = (isset($this->category['link_url']) && !empty($this->category['link_url'])) ? $this->category['link_url'] : "";
        $parent_id = (isset($this->category['parent_id']) && !empty($this->category['parent_id'])) ? $this->category['parent_id'] : NULL;

        $cat_arr[] = $cat_type;
        $cat_arr[] = $is_parent;

        if(!is_null($parent_id))
        {
            $parent_cat_details = Category::where('id',$parent_id)->first();
            $parent_cat_name = (isset($parent_cat_details['en_name'])) ? $parent_cat_details['en_name'] : '';
            if(empty($parent_cat_name))
            {
                $parent_cat_name = (isset($parent_cat_details['name'])) ? $parent_cat_details['name'] : '';
            }
        }
        $cat_arr[] = $parent_cat_name;

        if($cat_type == 'link')
        {
            $link_url = $link;
        }
        else
        {
            $link_url = "";
        }
        $cat_arr[] = $link_url;

        $all_excel_data[] = $cat_arr;

        // Insert Category Language Heading
        if(count($this->client_langs) > 0 && count($all_lang) > 0)
        {
            foreach($this->client_langs as $langkey => $lang)
            {
                $lang_code = $lang;
                $lang_id = $langkey;
                $lang_arr[] = $lang_code;
            }
            $all_excel_data[] = $lang_arr;
        }

        // Category Name By Language
        if(count($this->client_langs) > 0 && count($all_lang) > 0)
        {
            foreach($this->client_langs as $langkey => $lang)
            {
                $lang_code = $lang;
                $lang_id = $langkey;

                if(!empty($cat_dt) && !empty($lang_code))
                {
                    $name_code = $lang_code."_name";
                    $data[] = isset($cat_dt[$name_code]) ? $cat_dt[$name_code] : '';
                }

            }
            $all_excel_data[] = $data;
        }

        // Titles Array
        $title_data = [
            'AA',
            'LNG',
            'Product Name',
            'Description',
            'Price Descr 1',
            'Price 1',
            'Price Descr 2',
            'Price 2',
            'Price Descr 3',
            'Price 3',
            'Price Descr 4',
            'Price 4',
            'Price Descr 5',
            'Price 5',
            'Price Descr 6',
            'Price 6',
            'Price Descr 7',
            'Price 7',
            'Price Descr 8',
            'Price 8',
            'Price Descr 9',
            'Price 9',
            'Price Descr 10',
            'Price 10',
            'Images',
            'Tags',
            'Priority',
            'Divider',
            'Unpublished',
        ];
        $all_excel_data[] = $title_data;


        // Items Section
        if(count($this->client_langs) > 0 && count($all_lang) > 0)
        {
            foreach($this->client_langs as $langkey => $lang)
            {
                $lang_code = $lang;
                $lang_id = $langkey;

                $items = Items::where('category_id',$cat_dt['id'])->get();
                if(count($items) > 0)
                {
                    $inner_item_data = [];

                    foreach($items as $key => $item)
                    {
                        $item_name_key = $lang_code."_name";
                        $item_description_key = $lang_code."_description";
                        $item_data = [];

                        // No
                        $item_data[] = $key+1;

                        // Language Code
                        $item_data[] = $lang_code;

                        // Item Name
                        $item_data[] = isset($item[$item_name_key]) ? $item[$item_name_key] : '';

                        // Item Description
                        $item_data[] = isset($item[$item_description_key]) ? $item[$item_description_key] : '';

                        // Item Price
                        $item_prices = ItemPrice::where('item_id',$item['id'])->get();

                        if($item['type'] == 1)
                        {
                            if(count($item_prices) > 0)
                            {
                                $total = 10;
                                $item_total_prices = (count($item_prices) < 10) ? count($item_prices) : 10;
                                $total_price = $total - $item_total_prices;

                                foreach($item_prices as $key => $price)
                                {
                                    $price_label_key = $lang_code."_label";
                                    $item_data[] = isset($price[$price_label_key]) ? $price[$price_label_key] : '';
                                    $item_data[] = isset($price['price']) ? $price['price'] : '';
                                    if($key == 9)
                                    {
                                        break;
                                    }
                                }

                                if(($total_price > 0))
                                {
                                    for($i=1;$i<=$total_price;$i++)
                                    {
                                        $item_data[] = "";
                                        $item_data[] = "";
                                    }
                                }
                            }
                            else
                            {
                                for($i=1;$i<=10;$i++)
                                {
                                    $item_data[] = "";
                                    $item_data[] = "";
                                }
                            }
                        }
                        else
                        {
                            for($i=1;$i<=10;$i++)
                            {
                                $item_data[] = "";
                                $item_data[] = "";
                            }
                        }

                        $item_image = (isset($item['image'])) ? $item['image'] : '';

                        // Images
                        $item_data[] = $item_image;

                        // Tags
                        $item_tags = CategoryProductTags::with(['hasOneTag'])->where('category_id',$cat_dt['id'])->where('item_id',$item['id'])->get();

                        if(count($item_tags) > 0)
                        {
                            $tags = [];
                            foreach($item_tags as $tag)
                            {
                                $tag_name_key = $lang_code."_name";
                                $tags[] = isset($tag->hasOneTag[$tag_name_key]) ? $tag->hasOneTag[$tag_name_key] : '';
                            }
                            $imp_tags = (count($tags) > 0) ? implode(',',$tags) : $tags;
                            $item_data[] = $imp_tags;
                        }
                        else
                        {
                            $item_data[] = "";
                        }

                        // Priority
                        $item_data[] = "";

                        // Divider
                        $item_data[] = ($item['type'] == 2) ? '2' : '';

                        // Unpublished
                        $item_data[] = ($item['published'] == 0) ? '0' : '';

                        $inner_item_data[] = $item_data;
                    }
                    $all_excel_data[] = $inner_item_data;
                }
            }
        }

        return collect($all_excel_data);
    }


    // Sheet Heading
    public function headings(): array
    {
        $heading_arr = [];
        $heading_arr[] = 'Type';
        $heading_arr[] = 'Parent';
        $heading_arr[] = 'Parent Name';
        $heading_arr[] = 'Link';
        return $heading_arr;
    }


    // Sheets Title
    public function title(): string
    {
        $cat_name = (isset($this->category['en_name']) && !empty($this->category['en_name'])) ? strtoupper($this->category['en_name']) : strtoupper($this->category['name']);
        return $cat_name;
    }


    // Sheets Settings
    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event)
            {
                // Set Cell width
                $event->sheet->getDelegate()->getColumnDimension('A')->setWidth(35);
                $event->sheet->getDelegate()->getColumnDimension('B')->setWidth(35);
                $event->sheet->getDelegate()->getColumnDimension('C')->setWidth(35);
                $event->sheet->getDelegate()->getColumnDimension('D')->setWidth(38);
                $event->sheet->getDelegate()->getColumnDimension('E')->setWidth(15);
                $event->sheet->getDelegate()->getColumnDimension('G')->setWidth(15);
                $event->sheet->getDelegate()->getColumnDimension('I')->setWidth(15);
                $event->sheet->getDelegate()->getColumnDimension('K')->setWidth(15);
                $event->sheet->getDelegate()->getColumnDimension('M')->setWidth(15);
                $event->sheet->getDelegate()->getColumnDimension('O')->setWidth(15);
                $event->sheet->getDelegate()->getColumnDimension('Q')->setWidth(15);
                $event->sheet->getDelegate()->getColumnDimension('S')->setWidth(15);
                $event->sheet->getDelegate()->getColumnDimension('U')->setWidth(15);
                $event->sheet->getDelegate()->getColumnDimension('W')->setWidth(15);
                $event->sheet->getDelegate()->getColumnDimension('z')->setWidth(30);
                $event->sheet->getDelegate()->getColumnDimension('AC')->setWidth(15);

                // $event->sheet->getDelegate()->getStyle('A3:AC3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('ECF0F1');

                // Set Bold font of Header
                $event->sheet->getDelegate()->getStyle('A4:AC4')->getFont()->setBold(true);
                $event->sheet->getDelegate()->getStyle('A1:D1')->getFont()->setBold(true);
            },
        ];
    }
}
