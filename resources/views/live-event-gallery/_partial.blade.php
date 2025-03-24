@if (isset($data) && $data->isNotEmpty())
    <x-ui.base-datatable
        :headings="['#', 'Name', 'Date', 'Action']"
        :values="[
            [
                'key' => 'id',
                'type' => 'data'
            ],
            [
                'key' => 'name',
                'type' => 'data'
            ],
            [
                'key' => 'created_at',
                'type' => 'data'
            ],
            [
                'key' => 'manage',
                'type' => 'edit'
            ],
        ]"
        :data="$data"
        edit-route="live-event.edit"
        edit-id="id"
        model="data"
        table-striped
    >
    </x-ui.base-datatable>
@else
    No contacts found.
@endif
