@extends('client.layouts.client-layout')

@section('title', __('Item Reviews'))

@section('content')

    {{-- Page Title --}}
    <div class="pagetitle">
        <h1>{{ __('Item Reviews')}}</h1>
        <div class="row">
            <div class="col-md-8">
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('client.dashboard') }}">{{ __('Dashboard')}}</a></li>
                        <li class="breadcrumb-item active">{{ __('Item Reviews')}}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    {{-- Reviews Section --}}
    <section class="section dashboard">
        <div class="row">

            {{-- Reviews Card --}}
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped w-100" id="ingredientsTable">
                                <thead>
                                    <tr>
                                        <th>{{ __('Id')}}</th>
                                        {{-- <th>{{ __('Category')}}</th> --}}
                                        <th>{{ __('Item')}}</th>
                                        <th style="width: 25%">{{ __('Rating')}}</th>
                                        <th style="width: 30%">{{ __('Comment')}}</th>
                                        <th>{{ __('Email')}}</th>
                                        <th style="width: 15%">{{ __('Time') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($item_reviews as $review)
                                        <tr>
                                            <td>{{ $review->id }}</td>
                                            {{-- <td>{{ (isset($review->item->category['en_name'])) ? $review->item->category['en_name'] : '' }}</td> --}}
                                            <td>{{ (isset($review->item['en_name'])) ? $review->item['en_name'] : '' }}</td>
                                            <td>
                                                <div class="rated">
                                                    @for($i=1; $i <= $review->rating; $i++)
                                                        <label class="star-rating-complete" title="text">{{$i}} stars</label>
                                                    @endfor
                                                </div>
                                            </td>
                                            <td>{{ $review->comment }}</td>
                                            <td>{{ $review->email }}</td>
                                            <td style="white-space: nowrap;">{{ $review->created_at->diffForHumans(); }}</td>
                                            <td>
                                                <a onclick="delteItemReview({{ $review->id }})" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr class="text-center">
                                            <td colspan="7">{{ __('Reviews Not Found!')}}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection

{{-- Custom JS --}}
@section('page-js')
    <script type="text/javascript">

        // Function for Delete Review
        function delteItemReview(reviewID)
        {
            swal({
                title: "Enter Password to Delete It.",
                icon: "info",
                buttons: true,
                dangerMode: true,
                content: {
                    element: "input",
                    attributes: {
                        placeholder: "Enter Your Password",
                        type: "password",
                    },
                },
                closeOnClickOutside: false,
            })
            .then((passResponse) =>
            {
                if (passResponse == '')
                {
                    swal("Please Enter Password  to Delete Review!", {
                        icon: "info",
                    });
                }
                else if(passResponse == null)
                {
                    return false;
                }
                else
                {
                    $.ajax({
                        type: "POST",
                        url: "{{ route('verify.client.password') }}",
                        data: {
                            "_token" : "{{ csrf_token() }}",
                            "password" : passResponse,
                        },
                        dataType: "JSON",
                        success: function (response)
                        {
                            if(response.success == 1)
                            {
                                if(response.matched == 1)
                                {
                                    swal({
                                        title: "Are you sure You want to Delete It ?",
                                        icon: "warning",
                                        buttons: true,
                                        dangerMode: true,
                                    })
                                    .then((willDelReview) =>
                                    {
                                        if (willDelReview)
                                        {
                                            $.ajax({
                                                type: "POST",
                                                url: '{{ route("items.reviews.destroy") }}',
                                                data: {
                                                    "_token": "{{ csrf_token() }}",
                                                    'id': reviewID,
                                                },
                                                dataType: 'JSON',
                                                success: function(response)
                                                {
                                                    if (response.success == 1)
                                                    {
                                                        swal(response.message, {
                                                            icon: "success",
                                                        });
                                                        setTimeout(() => {
                                                            location.reload();
                                                        }, 1200);
                                                    }
                                                    else
                                                    {
                                                        toastr.error(response.message);
                                                    }
                                                }
                                            });
                                        }
                                        else
                                        {
                                            swal("Cancelled", "", "error");
                                        }
                                    });
                                }
                                else
                                {
                                    swal(response.message, {
                                        icon: "info",
                                    });
                                }
                            }
                            else
                            {
                                swal(response.message, {
                                    icon: 'error',
                                    title: 'Oops...',
                                });
                            }
                        }
                    });
                }
            });
        }

    </script>
@endsection
