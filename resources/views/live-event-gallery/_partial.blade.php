@if (isset($data) && $data->isNotEmpty())
    <x-ui.base-datatable
        :headings="['#', 'Name', 'Date', 'View',  'Edit', 'Delete']"
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
                'type' => 'view'
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
        view-route="live-event.show"
        view-id="id"
        delete-route="live-event.delete"
        edit-id="id"
        delete-id="id"
        model="live-event"
        :orderable="true"
        table-striped
    >
    </x-ui.base-datatable>
@else
    No items found.
@endif
