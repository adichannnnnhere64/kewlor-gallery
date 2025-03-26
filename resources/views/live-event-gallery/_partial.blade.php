@if (isset($data) && $data->isNotEmpty())
    <x-ui.base-datatable
        :headings="['#', 'Name', 'Date', 'Edit', 'Delete']"
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
            [
                'key' => 'manage',
                'type' => 'delete'
            ],
        ]"
        :data="$data"
        edit-route="live-event.edit"
        delete-route="live-event.delete"
        edit-id="id"
        delete-id="id"
        model="live-event"
        table-striped
    >
    </x-ui.base-datatable>
@else
    No contacts found.
@endif
