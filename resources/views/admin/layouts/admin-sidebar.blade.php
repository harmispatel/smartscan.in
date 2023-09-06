@php
    // UserDetails
    if (auth()->user())
    {
        $userID = encrypt(auth()->user()->id);
        $userName = auth()->user()->firstname." ".auth()->user()->lastname;
        $userImage = auth()->user()->image;
    }
    else
    {
        $userID = '';
        $userName = '';
        $userImage = '';
    }

    // Get Settings
    $settings = getAdminSettings();
    $favourite_client_limit = isset($settings['favourite_client_limit']) ? $settings['favourite_client_limit'] : 5;

    // Current Route Name
    $routeName = Route::currentRouteName();

    // Get Fav Clients List
    $clients = FavClients($favourite_client_limit);

@endphp

<aside id="sidebar" class="sidebar">
    <ul class="sidebar-nav" id="sidebar-nav">

        {{-- Dashboard Nav --}}
        <li class="nav-item">
            <a class="nav-link {{ ($routeName == 'admin.dashboard') ? 'active-tab' : '' }}" href="{{ route('admin.dashboard') }}">
                <i class="fa-solid fa-house-chimney {{ ($routeName == 'admin.dashboard') ? 'icon-tab' : '' }}"></i>
                <span>{{ __('Dashboard') }}</span>
            </a>
        </li>

        {{-- Clients Nav --}}
        <li class="nav-item">
            <a class="nav-link {{ (($routeName != 'clients') && ($routeName != 'clients.add') && ($routeName != 'clients.edit') && ($routeName != 'clients.list')) ? 'collapsed' : '' }} {{ (($routeName == 'clients') || ($routeName == 'clients.add') || ($routeName == 'clients.edit') || ($routeName == 'clients.list')) ? 'active-tab' : '' }}" data-bs-target="#client-nav" data-bs-toggle="collapse" href="#" aria-expanded="{{ (($routeName == 'clients') || ($routeName == 'clients.add') || ($routeName == 'clients.edit') || ($routeName == 'clients.list')) ? 'true' : 'false' }}">
                <i class="fa-solid fa-users {{ (($routeName == 'clients') || ($routeName == 'clients.add') || ($routeName == 'clients.edit') || ($routeName == 'clients.list')) ? 'icon-tab' : '' }}"></i><span>{{ __('Clients')}}</span><i class="bi bi-chevron-down ms-auto {{ (($routeName == 'clients') || ($routeName == 'clients.add') || ($routeName == 'clients.edit') || ($routeName == 'clients.list')) ? 'icon-tab' : '' }}"></i>
            <i class="bi bi-grid clients.list" onclick="gotoTilesView()"></i>
            </a>
            <ul id="client-nav" class="nav-content sidebar-ul collapse {{ (($routeName == 'clients') || ($routeName == 'clients.add') || ($routeName == 'clients.edit') || ($routeName == 'clients.list')) ? 'show' : '' }}" data-bs-parent="#sidebar-nav">
                @if(count($clients) > 0)
                    @foreach ($clients as $client)
                        @php
                            $shopName = (isset($client->hasOneShop->shop['name'])) ? $client->hasOneShop->shop['name'] : '';
                        @endphp
                        <li>
                            <a href="{{ route('clients.list',$client->id) }}">
                                -- &nbsp;<span>{{ $shopName }}</span>
                            </a>
                        </li>
                    @endforeach
                @endif
                <li>
                    <a href="{{ route('clients.list') }}">
                        <span>{{ __('All Clients')}}</span>
                    </a>
                </li>
            </ul>
        </li>

        {{-- Food Junctions Nav --}}
        <li class="nav-item">
            <a class="nav-link {{ ($routeName == 'food.junctions' || $routeName == 'food.junctions.create' || $routeName == 'food.junctions.edit') ? 'active-tab' : '' }}" href="{{ route('food.junctions') }}">
                <i class="fa-solid fa-hotel {{ ($routeName == 'food.junctions' || $routeName == 'food.junctions.create' || $routeName == 'food.junctions.edit') ? 'icon-tab' : '' }}"></i>
                <span>{{ __('Food Junctions')}}</span>
            </a>
        </li>

        {{-- Subscriptions Nav --}}
        <li class="nav-item">
            <a class="nav-link {{ (($routeName == 'subscriptions') || ($routeName == 'subscriptions.add') || ($routeName == 'subscriptions.edit')) ? 'active-tab' : '' }}" href="{{ route('subscriptions') }}">
                <i class="fa-solid fa-cubes {{ (($routeName == 'subscriptions') || ($routeName == 'subscriptions.add') || ($routeName == 'subscriptions.edit')) ? 'icon-tab' : '' }}"></i>
                <span>{{ __('Subscriptions')}}</span>
            </a>
        </li>

          {{-- Tutorial Nav --}}
          <li class="nav-item">
            <a class="nav-link {{ (($routeName == 'tutorial') || ($routeName == 'tutorial.add') || ($routeName == 'tutorial.edit')) ? 'active-tab' : '' }}" href="{{ route('tutorial') }}">
                <i class="fa-solid fa-circle-info  {{ (($routeName == 'tutorial') || ($routeName == 'tutorial.add') || ($routeName == 'tutorial.edit')) ? 'icon-tab' : '' }}"></i>
                <span>{{ __('Tutorial')}}</span>
            </a>
        </li>

        {{-- Ingredients Nav --}}
        <li class="nav-item">
            <a class="nav-link {{ (($routeName == 'ingredients') || ($routeName == 'ingredients.add') || ($routeName == 'ingredients.edit')) ? 'active-tab' : '' }}" href="{{ route('ingredients') }}">
                <i class="fas fa-seedling {{ (($routeName == 'ingredients') || ($routeName == 'ingredients.add') || ($routeName == 'ingredients.edit')) ? 'icon-tab' : '' }}"></i>
                <span>{{ __('Special Icons')}}</span>
            </a>
        </li>

        {{-- Import Export Nav --}}
        <li class="nav-item">
            <a class="nav-link {{ ($routeName == 'admin.import.export') ? 'active-tab' : '' }}" href="{{ route('admin.import.export') }}">
                <i class="fa-sharp fa-solid fa-file-export {{ ($routeName == 'admin.import.export') ? 'icon-tab' : '' }}"></i>
            <span>{{ __('Import / Export') }}</span>
            </a>
        </li>

        {{-- System Nav --}}
        <li class="nav-item">
            <a class="nav-link {{ (($routeName != 'admin.profile.view') && ($routeName != 'admin.settings') && ($routeName != 'admins')) ? 'collapsed' : '' }} {{ (($routeName == 'admin.profile.view') || ($routeName == 'admin.settings') || ($routeName == 'admins')) ? 'active-tab' : '' }}" data-bs-target="#system-nav" data-bs-toggle="collapse" href="#" aria-expanded="{{ (($routeName == 'admin.profile.view') || ($routeName == 'admin.settings') || ($routeName == 'admins')) ? 'true' : 'false' }}">
                <i class="fa-solid fa-wrench {{ (($routeName == 'admin.profile.view') || ($routeName == 'admin.settings') || ($routeName == 'admins')) ? 'icon-tab' : '' }}"></i><span>{{ __('System')}}</span><i class="bi bi-chevron-down ms-auto {{ (($routeName == 'admin.profile.view') || ($routeName == 'admin.settings') || ($routeName == 'admins')) ? 'icon-tab' : '' }}"></i>
            </a>
            <ul id="system-nav" class="nav-content sidebar-ul collapse {{ (($routeName == 'admin.profile.view') || ($routeName == 'admin.settings') || ($routeName == 'admins')) ? 'show' : '' }}" data-bs-parent="#sidebar-nav">
                <li>
                    <a href="{{ route('admin.profile.view',$userID) }}" class="{{ ($routeName == 'admin.profile.view') ? 'active-link' : '' }}">
                        {{-- <i class="{{ ($routeName == 'admin.profile.view') ? 'bi bi-circle-fill' : 'bi bi-circle' }}"></i>--}}
                        <span>{{ __('Profile')}}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admins') }}" class="{{ ($routeName == 'admins') ? 'active-link' : '' }}">
                        <span>{{ __('Admins')}}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.settings') }}" class="{{ ($routeName == 'admin.settings') ? 'active-link' : '' }}">
                        <span>{{ __('Settings')}}</span>
                    </a>
                </li>
            </ul>
        </li>

    </ul>
</aside>
