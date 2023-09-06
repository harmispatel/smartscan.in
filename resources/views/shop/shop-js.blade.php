@php
    // CustomerDetails
    if(session()->has('cust_details'))
    {
        $cust_details = session()->get('cust_details');

        if(isset($cust_details['user_name']) && !empty($cust_details['user_name']) && isset($cust_details['mobile_no']) && !empty($cust_details['mobile_no']))
        {
            $cust_access = 1;
        }
        else
        {
            $cust_access = 0;
        }
    }
    else
    {
        $cust_access = 0;
    }
@endphp

{{-- Bootstrap --}}
<script src="{{ asset('public/client/assets/js/bootstrap.min.js') }}"></script>

{{-- Jquery --}}
<script src="{{ asset('public/client/assets/js/jquery.min.js') }}"></script>

<script src="{{ asset('public/client/assets/js/swiper-bundle.min.js') }}"></script>

{{-- Toastr --}}
<script src="{{ asset('public/admin/assets/vendor/js/toastr.min.js') }}"></script>

{{-- Custom JS --}}
<script src="{{ asset('public/client/assets/js/custom.js') }}"></script>

{{-- Masonary --}}
<script src="{{ asset('public/client/assets/js/lightbox.js') }}"></script>

<script type="text/javascript" src="//translate.google.com/translate_a/element.js"></script>

<!-- move button  -->
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js"></script>

{{-- Common JS Functions --}}
<script type="text/javascript">

    // Toastr Settings
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-bottom-right",
        timeOut: 4000
    }

    // Function for Change Language
    function changeLanguage(langCode)
    {
        $.ajax({
            type: "POST",
            url: "{{ route('shop.locale.change') }}",
            data: {
                "_token" : "{{ csrf_token() }}",
                "lang_code" : langCode,
            },
            dataType: "JSON",
            success: function (response)
            {
                if(response.success == 1)
                {
                    location.reload();
                }
            }
        });
    }


    // Search Toggle
    $('#openSearchBox').on('click',function()
    {
        $(".search_input").addClass("d-block");
        $('#openSearchBox').addClass("d-none");
        $('#closeSearchBox').removeClass("d-none");
    });

    $('#closeSearchBox').on('click',function()
    {
        $("#closeSearchBox").addClass("d-none");
        $('#openSearchBox').removeClass("d-none");
        $(".search_input").removeClass("d-block");
    });

    // Open & Close Language Sidebar
    $('.lang_bt').on('click',function(){
        $(".lang_inr").addClass("sidebar");
    });
    $('.close_bt').on('click',function(){
        $(".lang_inr").removeClass("sidebar");
    });

    $(window).scroll(function()
    {
        var scroll = $(window).scrollTop();
        var header = $('.header_preview');
    });

    // Function for Get Item Details
    function getItemDetails(id,shopID)
    {
        // $('#itemDetailsModal').modal('show');
        $('#itemDetailsModal #item_dt_div').html('');

        $.ajax({
            type: "POST",
            url: "{{ route('items.get.details') }}",
            data: {
                "_token" : "{{ csrf_token() }}",
                "item_id" : id,
                "shop_id" : shopID,
            },
            dataType: "json",
            success: function (response)
            {
                if(response.success == 1)
                {
                    $('#itemDetailsModal #item_dt_div').html('');
                    $('#itemDetailsModal #item_dt_div').append(response.data);
                    $('#itemDetailsModal').modal('show');
                    updatePrice();
                }
                else
                {
                    toastr.error(response.message);
                }
            }
        });
    }

    // Update Price
    function updatePrice()
    {
        var base_price = 0.00;
        var radio_price = 0.00;
        var checkbox_price = 0.00;
        var def_currency = $('#def_currency').val();
        const option_ids = JSON.parse($('#option_ids').val());
        let quantity = $('#itemDetailsModal #quantity').val();

        if($('#itemDetailsModal input[name="base_price"]:checked').val() != undefined)
        {
            base_price = $('#itemDetailsModal input[name="base_price"]:checked').val();
        }

        if(option_ids.length > 0)
        {
            $.each(option_ids, function (opt_key, option_id)
            {
                var inner_radio = 0.00;
                if($('#itemDetailsModal input[name="option_price_radio_'+opt_key+'"]:checked').val())
                {
                    inner_radio = $('#itemDetailsModal input[name="option_price_radio_'+opt_key+'"]:checked').val();
                }

                var checkbox_array = $('input[name="option_price_checkbox_'+opt_key+'"]:checked').map(function () {
                    if(this.value)
                    {
                        checkbox_price += parseFloat(this.value);
                    }
                }).get();
                radio_price += parseFloat(inner_radio);
            });
        }

        base_price = (parseFloat(base_price) + parseFloat(radio_price) + parseFloat(checkbox_price)) * parseInt(quantity);
        base_price = base_price.toFixed(2);

        // Get Total with Currency
        $.ajax({
            type: "POST",
            url: "{{ route('total.with.currency') }}",
            data: {
                '_token' : "{{ csrf_token() }}",
                'total':base_price,
                'currency':def_currency,
            },
            dataType: "JSON",
            success: function (response)
            {
                if(response.success == 1)
                {
                    $('#itemDetailsModal #total_price').html('');
                    $('#itemDetailsModal #total_price').append(response.total);
                    $('#itemDetailsModal #total_amount').val(base_price);
                }
            }
        });

    }

    // Add to Cart
    function addToCart(itemId)
    {
        const option_ids = JSON.parse($('#option_ids').val());
        let cart_data = {};
        let categories_data = {};

        // Quantity
        cart_data['quantity'] = $('#itemDetailsModal #quantity').val();
        cart_data['total_amount'] = $('#itemDetailsModal #total_amount').val();
        cart_data['total_amount_text'] = $('#itemDetailsModal #total_price').html();
        cart_data['item_id'] = $('#itemDetailsModal #item_id').val();
        cart_data['shop_id'] = $('#itemDetailsModal #shop_id').val();
        cart_data['currency'] = $('#itemDetailsModal #def_currency').val();
        cart_data['option_id'] = $('#itemDetailsModal input[name="base_price"]:checked').attr('option-id');

        if(option_ids.length > 0)
        {
            $.each(option_ids, function (ids_key, option_id)
            {
                var options = [];
                // CheckBox Value
                $('#itemDetailsModal input[name="option_price_checkbox_'+ids_key+'"]:checked').map(function ()
                {
                    if(this.value)
                    {
                        var check_id = this.id;
                        var attr_val = $('#'+check_id).attr('opt_price_id');
                        options.push(attr_val);
                    }
                }).get();

                if(options.length > 0)
                {
                    categories_data[option_id] = options;
                }

                // Radio Button Value
                if($('#itemDetailsModal input[name="option_price_radio_'+ids_key+'"]:checked').val())
                {
                    categories_data[option_id] = $('#itemDetailsModal input[name="option_price_radio_'+ids_key+'"]:checked').attr('opt_price_id');
                }
            });
            cart_data['categories_data'] = JSON.stringify(categories_data);
        }

        $.ajax({
            type: "POST",
            url: "{{ route('shop.add.to.cart') }}",
            data: {
                '_token': "{{ csrf_token() }}",
                'cart_data':cart_data,
            },
            dataType: "JSON",
            success: function (response)
            {
                if(response.success == 1)
                {
                    $('#itemDetailsModal #item_dt_div').html('');
                    $('#itemDetailsModal').modal('hide');
                    toastr.success(response.message);
                    setTimeout(() => {
                        location.reload();
                    }, 1200);
                }
                else
                {
                    $('#itemDetailsModal #item_dt_div').html('');
                    $('#itemDetailsModal').modal('hide');
                    toastr.error(response.message);
                }
            }
        });
    }


    // Quantity Increment Decrement using +,- Button
    function QuntityIncDec(ele)
    {
        var fieldName = $(ele).attr('data-field');
        var type = $(ele).attr('data-type');
        var input = $("input[name='"+fieldName+"']");
        var currentVal = parseInt(input.val());
        var name = $(input).attr('name');

        if (!isNaN(currentVal))
        {
            if(type == 'minus')
            {
                if(currentVal > input.attr('min'))
                {
                    input.val(currentVal - 1).change();
                }

                if(parseInt(input.val()) == input.attr('min'))
                {
                    $(ele).attr('disabled', true);
                }
            }
            else if(type == 'plus')
            {
                if(currentVal < input.attr('max'))
                {
                    input.val(currentVal + 1).change();
                }

                if(parseInt(input.val()) == input.attr('max'))
                {
                    $(ele).attr('disabled', true);
                }
            }
        }
        else
        {
            input.val(1);
        }

        var changedVal = parseInt(input.val());
        var minValue =  parseInt($(input).attr('min'));
        var maxValue =  parseInt($(input).attr('max'));

        if(changedVal > minValue)
        {
            $(".btn-number[data-type='minus'][data-field='"+name+"']").removeAttr('disabled');
        }

        if(changedVal < maxValue)
        {
            $(".btn-number[data-type='plus'][data-field='"+name+"']").removeAttr('disabled');
        }

        updatePrice();
    }

    // Quantity Increment Decrement using Onchange
    function QuntityIncDecOnChange(ele)
    {
        var minValue =  parseInt($(ele).attr('min'));
        var maxValue =  parseInt($(ele).attr('max'));
        var valueCurrent = parseInt($(ele).val());
        var name = $(ele).attr('name');

        if(!$.isNumeric(valueCurrent))
        {
            alert('Sorry, Please Enter Valid Quantity Number');
            $(ele).val(1);
            updatePrice();
            return false;
        }

        if(valueCurrent >= minValue)
        {
            $(".btn-number[data-type='minus'][data-field='"+name+"']").removeAttr('disabled')
        }
        else
        {
            alert('Sorry, the minimum value was reached');
            $(ele).val(1);
        }

        if(valueCurrent <= maxValue)
        {
            $(".btn-number[data-type='plus'][data-field='"+name+"']").removeAttr('disabled')
        }
        else
        {
            alert('Sorry, the maximum value was reached');
            $(ele).val(1);
        }
        updatePrice();
    }

    // Function for Submit Item Review & Rating
    function submitItemReview()
    {
        // Clear all Toastr Messages
        toastr.clear();

        var myFormData = new FormData(document.getElementById('reviewForm'));

        $.ajax({
            type: "POST",
            url: "{{ route('send.item.review') }}",
            data: myFormData,
            dataType: "JSON",
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function()
            {
                $('#btn-review').hide();
                $('#load-btn-review').show();
            },
            success: function (response)
            {
                if(response.success == 1)
                {
                    $('#btn-review').show();
                    $('#load-btn-review').hide();
                    $('#reviewForm').trigger("reset");
                    // $('#item_review').val('');
                    // $("input[name='rating']").removeAttr('checked');
                    // $("#star3").prop('checked', true);
                    toastr.success(response.message);
                }
                else
                {
                    toastr.error(response.message);
                    $('#itemDetailsModal').modal('hide');
                }
            },
            error: function(response)
            {
                if(response.responseJSON.errors)
                {
                    $('#btn-review').show();
                    $('#load-btn-review').hide();

                    $.each(response.responseJSON.errors, function (i, error)
                    {
                        toastr.error(error);
                    });
                }
            }
        });
    }

    // Auto Translate
    $('#auto_translate').on('change',function(){
        var isChecked = $(this).prop('checked');
        if(isChecked == true)
        {
            new google.translate.TranslateElement({pageLanguage: 'en'}, 'translated_languages');
            $('.goog-te-combo').addClass('form-select');
        }
    });


    // Payment Modal
    $('.pay-now-btn').on('click',function(){
        $('#PaymentModal').modal('show');
    });

    $('#PaymentModal .btn-close').on('click',function(){
        $('#payment_amount').val('');
    });

    // Payment
    $('.pay-btn').on('click',function(){
        var amount = $('#payment_amount').val();

        $('#gpay_btn').removeAttr("href");
        $('#phonepe_btn').removeAttr("href");
        $('#paytm_btn').removeAttr("href");

        if(amount > 0)
        {
            var payType = $(this).attr('pay-type');

            if(payType == 'gpay')
            {
                // var link = 'gpay://upi/pay?pa={{ $upi_id }}&pn={{ $payee_name }}&am='+amount+'&cu={{ $currency }}';
                var link = 'gpay://upi/pay?pa={{ $upi_id }}&pn={{ $payee_name }}&am='+amount+'&cu=INR';
            }
            else if(payType == 'phonepe')
            {
                // var link = 'phonepe://upi/pay?pa={{ $upi_id }}&pn={{ $payee_name }}&am='+amount+'&cu={{ $currency }}';
                var link = 'phonepe://upi/pay?pa={{ $upi_id }}&pn={{ $payee_name }}&am='+amount+'&cu=INR';
            }
            else if(payType == 'paytm')
            {
                // var link = 'paytm://upi/pay?pa={{ $upi_id }}&pn={{ $payee_name }}&am='+amount+'&cu={{ $currency }}';
                var link = 'paytm://upi/pay?pa={{ $upi_id }}&pn={{ $payee_name }}&am='+amount+'&cu=INR';
            }

            $(this).attr("href",link);
            $(this).click();
        }
        else
        {
            alert("Please Enter Valid Amount.");
            return false;
        }
    });


    // moveble button
    $(document).ready(function()
    {
        $('#pay_now_btn').draggable();

        var custAcess = @json($cust_access);
        var custDetails = @json($customer_details);
        if(custAcess == 0 && custDetails == 1)
        {
            $('#customerDetailsModal').modal('show');
        }
    });


    // Function for Save CustomerDetails
    function SaveCustDetails()
    {
        // Clear all Toastr Messages
        toastr.clear();

        var myFormData = new FormData(document.getElementById('custDetailsForm'));

        $.ajax({
            type: "POST",
            url: "{{ route('save.customer.details') }}",
            data: myFormData,
            dataType: "JSON",
            contentType: false,
            cache: false,
            processData: false,
            success: function (response)
            {
                if(response.success == 1)
                {
                    toastr.success(response.message);
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                }
                else
                {
                    toastr.error(response.message);
                }
            },
            error: function(response)
            {
                if(response.responseJSON?.errors)
                {
                    $.each(response.responseJSON.errors, function (i, error)
                    {
                        toastr.error(error);
                    });
                }
            }
        });
    }


</script>

