<?php

use Illuminate\Support\Facades\Http;

use function Laravel\Folio\{middleware, name};
use function Livewire\Volt\{state, with};
use Livewire\Volt\Component;
use App\Models\LiveEventGallery;
use Livewire\WithPagination;

name('live-event');
middleware(['auth', 'verified', 'can:access-admin-panel']);

new class extends Component {
    use WithPagination;

    public function with(): array
    {
        return [
            'data' => LiveEventGallery::query()->orderBy('order_column')->paginate(1000),
        ];
    }

    public function updateOrder(LiveEventGallery $model, $item)
    {
        $ids = collect(LiveEventGallery::query()->orderBy('order_column')->get())->pluck('id');
        $updated = $ids->reject(fn($id) => $id == $model->id);
        $updated->splice($item, 0, [$model->id]);
        LiveEventGallery::setNewOrder($updated->toArray());
    }

    public function mount(): void
    {
        //       $this->data = $data;
    }
};

#with(fn () => ['posts' => 'adicchans']);

?>

<style>
    .dark img[alt="Kewlor Logo"] {
        filter: invert(1);
    }
</style>

<x-layouts.app>

    <x-slot name="header">
        <h2 class="text-lg font-semibold leading-tight text-gray-800 dark:text-gray-200">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>


    @volt('live-event')
        <div>
            <div x-data="{
                // checks any search query string in browser URL
                query: new URLSearchParams(location.search).get('s') || '',

                // fetches data using fetch api
                fetchData(page = null) {
                    let currentPageFromUrl = location.search.match(/page=(\d+)/) ?
                        location.search.match(/page=(\d+)/)[1] :
                        1

                    if (this.query) {
                        currentPageFromUrl = 1;
                        history.pushState(null, null, '?page=1&search=' + this.query);
                    }

                    const endpointURL = page !== null ?
                        `${page}&search=${this.query}` :
                        `/live-event?page=${currentPageFromUrl}&search=${this.query}`;

                    if (page) {

                        const urlObj = new URL(page);

                        const params = new URLSearchParams(urlObj.search);

                        history.pushState(null, null, '?page=' + params.get('page'));
                    }

                    fetch(endpointURL, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.text())
                        .then(html => {
                            document.querySelector('#js-contacts-body').innerHTML = html
                        })
                }
            }" x-init="$watch('query', (value) => {
                const url = new URL(window.location.href);
                url.searchParams.set('search', value);
                history.pushState(null, document.title, url.toString());
            })" @goto-page="fetchData($event.detail.page)"
                @reload.window="fetchData()" x-cloak>

                <div class="my-4">
                    <a href="{{ route('live-event.create') }}"
                        class="my-4 px-4 py-2 bg-primary-600 text-white rounded">Create</a>
                    <input type="text" placeholder="Search"
                        class="my-4 appearance-none flex w-full h-10 px-3 py-2 text-sm bg-white dark:text-gray-300 dark:bg-white/[4%] border rounded-md border-gray-300 dark:border-white/10 ring-offset-background placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:border-gray-300 dark:focus:border-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-200/60 dark:focus:ring-white/20 disabled:cursor-not-allowed disabled:opacity-50"
                        x-model="query" x-on:input.debounce.750="fetchData()" />
                </div>

                <div>


                </div>
                <div id="js-contacts-body">
                    @include('live-event-gallery._partial')
                </div>
            </div>

        </div>
    @endvolt
</x-layouts.app>
