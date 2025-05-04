
<div>
    <div
        class="p-2 transition-transform duration-300 bg-white border shadow-sm cursor-pointer group dark:bg-gray-900 rounded-xl hover:-translate-y-1 hover:shadow-xl border-slate-100 dark:border-white/10">
        <a href="{{ $detailsUrl }}?event={{ $liveEventId }}">
            <img src="{{ $image }}" class="object-cover w-full lg:h-[200px] rounded-md" />
        </a>
        <div class="flex flex-col justify-start px-1 py-2">
            <span class="line-clamp-1 pt-2.5 text-md font-semibold flex items-center dark:text-white space-x-1.5">
                <span class="truncate">{{ $title ?? '' }}</span>
            </span>
            <p class="block pt-1.5 pb-3 text-xs text-left line-clamp-2 text-slate-800/60 dark:text-white/50">
                {{ $description }}
            </p>
            @if (isset($categories) && count($categories))
                <div class="flex space-x-1 mb-2 relative w-full flex-wrap space-y-1">
                    @foreach ($categories as $key => $category)
                        <span wire:key="category-{{ $key }}" class="max-w-[100px] inline-block">
                            <x-ui.badge background="bg-gray-400" color="text-white">{{ $category }}</x-ui.badge>
                        </span>
                    @endforeach

                </div>
            @endif
            <a href="{{ $detailsUrl }}?event={{ $liveEventId }}"
                class="px-4 py-2 text-xs font-medium rounded-md bg-white border text-gray-500 hover:text-gray-700 border-gray-200/70 dark:focus:ring-offset-gray-900 dark:border-gray-400/10 hover:bg-gray-50 active:bg-white dark:focus:ring-gray-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200/60 dark:bg-gray-800/50 dark:hover:bg-gray-800/70 dark:text-gray-400 focus:shadow-outline cursor-pointer inline-flex items-center w-full justify-center disabled:opacity-50 font-semibold focus:outline-none">
                View Details
            </a>

            <div class="mt-2.5 flex items-center space-x-1.5">
            </div>
        </div>


        @if ($showComment)
            <div class="flex items-center justify-end text-lg space-x-2 lg:text-sm  lg:items-end px-2 text-gray-500">

                <span class="flex items-center space-x-1">
                    <span>{{ $commentsCount }}</span> <svg xmlns="http://www.w3.org/2000/svg"
                        class="h-6 w-6 lg:h-4 lg:w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="icon icon-tabler icons-tabler-outline icon-tabler-message">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M8 9h8" />
                        <path d="M8 13h6" />
                        <path
                            d="M18 4a3 3 0 0 1 3 3v8a3 3 0 0 1 -3 3h-5l-5 3v-3h-2a3 3 0 0 1 -3 -3v-8a3 3 0 0 1 3 -3h12z" />
                    </svg>
                </span>
                <div>

                </div>
                <div class="flex">
                    @if ($id)
                        <div class="flex space-x-1" >
                            <div  class="dark:text-white">{{ $likesCount }}</div>
                            <button wire:click="like({{ $id }})">
                                <span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 lg:h-4 lg:w-4"
                                        viewBox="0 0 24 24" fill="none" stroke="#656172" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-thumb-up">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path
                                            d="M7 11v8a1 1 0 0 1 -1 1h-2a1 1 0 0 1 -1 -1v-7a1 1 0 0 1 1 -1h3a4 4 0 0 0 4 -4v-1a2 2 0 0 1 4 0v5h3a2 2 0 0 1 2 2l-1 5a2 3 0 0 1 -2 2h-7a3 3 0 0 1 -3 -3" />
                                    </svg>
                                </span>

                            </button>
                        </div>

                        <div class="px-1">|</div>


                        <div class="flex space-x-1" >
                            <div  class="dark:text-white">{{ $dislikesCount }}</div>
                            <button wire:click="dislike({{ $id }})">
                                <span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 lg:h-4 lg:w-4"
                                        viewBox="0 0 24 24" fill="none" stroke="#656172" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-thumb-down">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path
                                            d="M7 13v-8a1 1 0 0 0 -1 -1h-2a1 1 0 0 0 -1 1v7a1 1 0 0 0 1 1h3a4 4 0 0 1 4 4v1a2 2 0 0 0 4 0v-5h3a2 2 0 0 0 2 -2l-1 -5a2 3 0 0 0 -2 -2h-7a3 3 0 0 0 -3 3" />
                                    </svg>

                                </span>

                            </button>
                        </div>
                    @endif
                </div>
            </div>

        @endif
        <div class="flex justify-end mt-1 text-sm text-gray-500">
        </div>
    </div>
</div>
